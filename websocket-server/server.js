const WebSocket = require('ws');
const axios = require('axios');

const wss = new WebSocket.Server({ port: 8080 });

const userPositions = new Map();

const BACKEND_BASE_URL = 'http://localhost:8000';


const clients = new Map();

function broadcastToSameRoomClients(sourceSpacesMemberID) {
    const sourceUser = userPositions.get(sourceSpacesMemberID);
    if (!sourceUser) return;

    // Cari user yang di room sama
    const usersInSameRoom = [];
    for (const user of userPositions.values()) {
        if (user.RoomID === sourceUser.RoomID) {
            usersInSameRoom.push({
                id: user.SpacesMember_ID,
                Username: user.Username,
                PosX: user.PosX,
                PosY: user.PosY,
                FacingDirection: user.FacingDirection,
                ChatArea_ID: user.ChatArea_ID || null,
                Character_ID: user.Character_ID
            });

        }
    }

    // Broadcast ke semua client yang di room sama
    usersInSameRoom.forEach(user => {
        const ws = clients.get(String(user.id));
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({
                type: 'same_room_result',
                sender: sourceSpacesMemberID,
                data: usersInSameRoom
            }));
        }
    });
}

wss.on('connection', function connection(ws) {
    console.log('Client connected');

    let memberId = null;

    ws.on('message', async function incoming(message) {
        try {
            const data = JSON.parse(message);
            const { type, payload } = data;

            console.log(`[WebSocket] Received:`, data);

            if (type === 'register') {
                if (!payload || !payload.SpacesMember_ID || !payload.Username || !payload.RoomID || !payload.Character_ID) {
                    ws.send(JSON.stringify({ type: 'error', message: 'register needs SpacesMember_ID, Username, RoomID, Character_ID' }));
                    return;
                }

                memberId = String(payload.SpacesMember_ID);
                clients.set(memberId, ws);

                userPositions.set(memberId, {
                    SpacesMember_ID: memberId,
                    Username: payload.Username,
                    RoomID: payload.RoomID,
                    Character_ID: payload.Character_ID, // <-- Simpan character_ID
                    PosX: payload.PosX || 0,
                    PosY: payload.PosY || 0,
                    FacingDirection: payload.FacingDirection || 'down',
                    ChatArea_ID: null
                });

                console.log(`[WebSocket] Registered user ${memberId} with character ${payload.Character_ID}`);
                broadcastToSameRoomClients(memberId);
                return;
            }


            if (!payload || !payload.SpacesMember_ID) {
                ws.send(JSON.stringify({ type: 'error', message: 'Missing SpacesMember_ID in payload' }));
                return;
            }

            const SpacesMember_ID = String(payload.SpacesMember_ID);
            const user = userPositions.get(SpacesMember_ID);
            if (!user) {
                ws.send(JSON.stringify({ type: 'error', message: 'User not registered' }));
                return;
            }

            switch (type) {
                case 'moveroom':
                    if (!payload.NewRoomID) {
                        ws.send(JSON.stringify({ type: 'error', message: 'moveroom requires NewRoomID' }));
                        return;
                    }

                    user.RoomID = payload.NewRoomID;
                    user.PosX = payload.PosX || 0;
                    user.PosY = payload.PosY || 0;
                    user.FacingDirection = payload.FacingDirection || 'down';

                    try {
                        await axios.put(`${BACKEND_BASE_URL}/userposition/moveroom/${SpacesMember_ID}`);
                        console.log(`[WebSocket] moveroom success for SpacesMember_ID ${SpacesMember_ID}`);
                        broadcastToSameRoomClients(SpacesMember_ID);
                    } catch (err) {
                        console.error(`[WebSocket] moveroom failed for ${SpacesMember_ID}:`, err.message);
                    }
                    break;

                case 'move':
                    if (!payload.direction) {
                        ws.send(JSON.stringify({ type: 'error', message: 'move requires direction' }));
                        return;
                    }
                    switch (payload.direction) {
                        case 'up':
                            user.PosY -= 1;
                            user.FacingDirection = 'up';
                            break;
                        case 'down':
                            user.PosY += 1;
                            user.FacingDirection = 'down';
                            break;
                        case 'left':
                            user.PosX -= 1;
                            user.FacingDirection = 'left';
                            break;
                        case 'right':
                            user.PosX += 1;
                            user.FacingDirection = 'right';
                            break;
                    }
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'update':
                    if (!payload.updateData) {
                        ws.send(JSON.stringify({ type: 'error', message: 'update requires updateData' }));
                        return;
                    }
                    Object.assign(user, payload.updateData);
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'getSameRoom':
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'enterchat':
                    if (!payload.ChatArea_ID) {
                        ws.send(JSON.stringify({ type: 'error', message: 'enterchat requires ChatArea_ID' }));
                        return;
                    }
                    user.ChatArea_ID = payload.ChatArea_ID;
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'leavechat':
                    user.ChatArea_ID = null;
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'leave':
                    userPositions.delete(SpacesMember_ID);
                    clients.delete(SpacesMember_ID);
                    console.log(`[WebSocket] User ${SpacesMember_ID} left and removed`);
                    broadcastToSameRoomClients(SpacesMember_ID);
                    break;

                case 'player_moved':
                    if (typeof payload.PosX !== 'number' || typeof payload.PosY !== 'number' || !payload.FacingDirection) {
                        ws.send(JSON.stringify({ type: 'error', message: 'player_moved requires PosX, PosY, and FacingDirection' }));
                        return;
                    }

                    user.PosX = payload.PosX;
                    user.PosY = payload.PosY;
                    user.FacingDirection = payload.FacingDirection;

                    console.log(`[WebSocket] User ${SpacesMember_ID} moved to (${user.PosX}, ${user.PosY}) facing ${user.FacingDirection}`);

                    for (const [id, clientWs] of clients.entries()) {
                        if (id !== SpacesMember_ID && clientWs.readyState === WebSocket.OPEN) {
                            const targetUser = userPositions.get(id);
                            if (targetUser && targetUser.RoomID === user.RoomID) {
                                clientWs.send(JSON.stringify({
                                    type: 'player_moved',
                                    sender: SpacesMember_ID,
                                    data: {
                                        SpacesMember_ID,
                                        PosX: user.PosX,
                                        PosY: user.PosY,
                                        FacingDirection: user.FacingDirection
                                    }
                                }));
                            }
                        }
                    }
                    break;
                
                    case 'send_chat_message': {
                        const { Chats_ID, ChatsMember_ID, Message, Username, Profile_Image } = payload;

                        if (!Chats_ID || !ChatsMember_ID || !Message || !Username || !Profile_Image) {
                            ws.send(JSON.stringify({ type: 'error', message: 'send_chat_message requires Chats_ID, ChatsMember_ID, Message, Username, Profile_Image' }));
                            return;
                        }

                        try {
                            // Simpan ke backend
                            const response = await axios.post(`${BACKEND_BASE_URL}/chat_messages`, {
                                Chats_ID,
                                ChatsMember_ID,
                                Message
                            });

                            const savedMessage = response.data;

                            const broadcastPayload = {
                                type: 'chat_message',
                                data: {
                                    Chats_ID,
                                    ChatsMember_ID,
                                    Message,
                                    Username,
                                    Profile_Image,
                                    created_at: savedMessage?.created_at || new Date().toISOString()
                                }
                            };

                            // Broadcast ke client yang ada di Chats_ID yang sama
                            for (const [memberId, clientWs] of clients.entries()) {
                                if (clientWs.readyState === WebSocket.OPEN) {
                                    const user = userPositions.get(memberId);
                                    if (user && user.ChatArea_ID === Chats_ID) {
                                        clientWs.send(JSON.stringify(broadcastPayload));
                                    }
                                }
                            }

                            console.log(`[WebSocket] Broadcasted message from ${Username} in Chats_ID ${Chats_ID}`);
                        } catch (err) {
                            console.error('[WebSocket] Failed to send chat message to API:', err.message);
                            ws.send(JSON.stringify({ type: 'error', message: 'Failed to send message to server' }));
                        }

                        break;
                    }


                default:
                    ws.send(JSON.stringify({ type: 'error', message: `Unknown type: ${type}` }));
            }

        } catch (err) {
            console.error('[WebSocket] Error handling message:', err);
            ws.send(JSON.stringify({ type: 'error', message: err.message }));
        }
    });

    ws.on('close', () => {
        if (memberId) {
            userPositions.delete(memberId);
            clients.delete(memberId);
            console.log(`[WebSocket] Disconnected and removed user ${memberId}`);
            broadcastToSameRoomClients(memberId);
        }
    });
});

console.log('WebSocket server running on ws://localhost:8080');
