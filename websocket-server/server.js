const WebSocket = require('ws');
const axios = require('axios');

const wss = new WebSocket.Server({ port: 8080 });
const userPositions = new Map();
const BACKEND_BASE_URL = 'http://localhost:8000';
const clients = new Map();
const typingTimers = new Map();

function broadcastToSameRoomClients(sourceSpacesMemberID) {
    const sourceUser = userPositions.get(sourceSpacesMemberID);
    if (!sourceUser) return;

    const usersInSameRoom = [];
    for (const user of userPositions.values()) {
        if (user.RoomID === sourceUser.RoomID) {
            // Sertakan data yang lebih kaya di sini
            usersInSameRoom.push({
                id: user.SpacesMember_ID,
                Username: user.Username,
                PosX: user.PosX,
                PosY: user.PosY,
                FacingDirection: user.FacingDirection,
                ChatArea_ID: user.ChatArea_ID || null,
                Character_ID: user.Character_ID,
                // --- TAMBAHAN DATA BARU ---
                Active_Mic: user.Active_Mic,
                Active_Video: user.Active_Video,
                Role: user.Role || "Member" // Default role
            });
        }
    }

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

// --- FUNGSI BARU UNTUK CHAT ---
// Dibuat persis meniru logika broadcastToSameRoomClients
function broadcastToSameChatClients(sourceChatsID, payloadToBroadcast) {
    console.log(`[ChatBroadcast] Memulai siaran untuk ChatID: ${sourceChatsID}`);
    let broadcastCount = 0;

    // Langkah 1: Kumpulkan semua user yang ada di chat yang sama
    const usersInSameChat = [];
    for (const user of userPositions.values()) {
        if (user.ActiveChatsID === sourceChatsID) {
            usersInSameChat.push(user);
        }
    }

    // Langkah 2: Kirim pesan ke setiap user yang sudah dikumpulkan
    usersInSameChat.forEach(targetUser => {
        const ws = clients.get(String(targetUser.SpacesMember_ID));
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(payloadToBroadcast));
            broadcastCount++;
        }
    });

    console.log(`[ChatBroadcast] Pesan berhasil dikirim ke ${broadcastCount} pengguna.`);
}


wss.on('connection', function connection(ws) {
    console.log('Client connected');
    let memberId = null;

    ws.on('message', async function incoming(message) {
        try {
            const data = JSON.parse(message);
            const { type, payload } = data;
            const user = memberId ? userPositions.get(memberId) : null;

            // Pastikan user sudah terdaftar untuk semua aksi kecuali 'register'
            if (type !== 'register' && !user) {
                return ws.send(JSON.stringify({ type: 'error', message: 'Connection not registered or user not found' }));
            }

            switch (type) {
                case 'register':
                    memberId = String(payload.SpacesMember_ID);
                    clients.set(memberId, ws);
                    userPositions.set(memberId, {
                        SpacesMember_ID: memberId,
                        Username: payload.Username,
                        RoomID: payload.RoomID,
                        Character_ID: payload.Character_ID,
                        PosX: payload.PosX || 0,
                        PosY: payload.PosY || 0,
                        FacingDirection: payload.FacingDirection || 'down',
                        ChatArea_ID: null,
                        ActiveChatsID: null,
                        Active_Mic: false,
                        Active_Video: false
                    });
                    console.log(`[WebSocket] Registered user ${memberId}`);
                    broadcastToSameRoomClients(memberId);
                    break;

                case 'join_chat':
                    if (payload.Chats_ID) {
                        user.ActiveChatsID = payload.Chats_ID;
                        console.log(`[WebSocket] User ${memberId} joined chat ${user.ActiveChatsID}`);
                    }
                    break;
                
                case 'start_typing': {
                    // Jika ada timer lama untuk user ini, hapus dulu
                    if (typingTimers.has(memberId)) {
                        clearTimeout(typingTimers.get(memberId));
                    }

                    // Siarkan ke semua ORANG LAIN di chat yang sama
                    const typingPayload = {
                        type: 'user_is_typing',
                        data: { Username: user.Username }
                    };
                    
                    // Kita siarkan ke semua orang kecuali diri sendiri
                    for (const [targetMemberId, clientWs] of clients.entries()) {
                        if (targetMemberId !== memberId) { // <-- Kunci: Jangan kirim ke diri sendiri
                            const targetUser = userPositions.get(targetMemberId);
                            if (targetUser && targetUser.ActiveChatsID === user.ActiveChatsID && clientWs.readyState === WebSocket.OPEN) {
                                clientWs.send(JSON.stringify(typingPayload));
                            }
                        }
                    }

                    // Atur timer baru. Jika timer ini selesai, berarti user berhenti mengetik
                    const timer = setTimeout(() => {
                        const stoppedTypingPayload = {
                            type: 'user_stopped_typing',
                            data: { Username: user.Username }
                        };
                        // Siarkan pesan 'berhenti mengetik'
                         for (const [targetMemberId, clientWs] of clients.entries()) {
                            if (targetMemberId !== memberId) {
                                const targetUser = userPositions.get(targetMemberId);
                                if (targetUser && targetUser.ActiveChatsID === user.ActiveChatsID && clientWs.readyState === WebSocket.OPEN) {
                                    clientWs.send(JSON.stringify(stoppedTypingPayload));
                                }
                            }
                        }
                        typingTimers.delete(memberId);
                    }, 3000); // Anggap berhenti setelah 3 detik tidak ada ketikan baru

                    typingTimers.set(memberId, timer);
                    break;
                }

                case 'send_chat_message': {
                    const { Chats_ID, Message, Username, Profile_Image } = payload;
                    const broadcastPayload = {
                        type: 'chat_message',
                        data: { Message, Username, Profile_Image }
                    };

                    broadcastToSameChatClients(Chats_ID, broadcastPayload);

                    try {
                        await axios.post(`${BACKEND_BASE_URL}/chat_messages`, {
                            Chats_ID: payload.Chats_ID,
                            ChatsMember_ID: payload.ChatsMember_ID,
                            Message: payload.Message
                        });
                    } catch (err) {
                        console.error('[API] Failed to save chat message:', err.message);
                    }
                    break;
                }
                
                case 'moveroom':
                    if (payload.NewRoomID) {
                        user.RoomID = payload.NewRoomID;
                        user.PosX = payload.PosX || 0;
                        user.PosY = payload.PosY || 0;
                        user.FacingDirection = payload.FacingDirection || 'down';
                        broadcastToSameRoomClients(memberId);
                    }
                    break;

                case 'move':
                    if (payload.direction) {
                        switch (payload.direction) {
                            case 'up': user.PosY -= 1; user.FacingDirection = 'up'; break;
                            case 'down': user.PosY += 1; user.FacingDirection = 'down'; break;
                            case 'left': user.PosX -= 1; user.FacingDirection = 'left'; break;
                            case 'right': user.PosX += 1; user.FacingDirection = 'right'; break;
                        }
                        broadcastToSameRoomClients(memberId);
                    }
                    break;

                case 'update':
                    if (payload.updateData) {
                        Object.assign(user, payload.updateData);
                        broadcastToSameRoomClients(memberId);
                    }
                    break;

                case 'getSameRoom':
                    broadcastToSameRoomClients(memberId);
                    break;

                case 'enterchat':
                    if (payload.ChatArea_ID) {
                        user.ChatArea_ID = payload.ChatArea_ID;
                        broadcastToSameRoomClients(memberId);
                    }
                    break;

                case 'leavechat':
                    user.ChatArea_ID = null;
                    broadcastToSameRoomClients(memberId);
                    break;

                case 'update_status': {
                    if (!payload.updateData) break;

                    try {
                        // 1. Simpan perubahan ke database melalui API (seperti permintaanmu)
                        await axios.put(`${BACKEND_BASE_URL}/spacemember/status/${memberId}`, payload.updateData);
                        
                        // 2. Jika berhasil, perbarui ingatan di server
                        Object.assign(user, payload.updateData);
                        console.log(`[WebSocket] Status updated for ${user.Username}:`, payload.updateData);

                        // 3. Siarkan perubahan ke semua orang di ruangan
                        broadcastToSameRoomClients(memberId);

                    } catch (err) {
                        console.error(`[API Error] Failed to update status for ${memberId}:`, err.message);
                    }
                    break;
                }
                
                default:
                    console.log(`Received unhandled message type: ${type}`);
            }
        } catch (err) {
            console.error('[WebSocket] Error handling message:', err);
        }
    });

    ws.on('close', () => {
        if (memberId) {
            const disconnectedUser = userPositions.get(memberId);
            clients.delete(memberId);
            userPositions.delete(memberId);
            console.log(`[WebSocket] Disconnected and removed user ${memberId}`);
            if (disconnectedUser) {
                // Memberi tahu klien lain bahwa user ini telah pergi
                broadcastToSameRoomClients(memberId); 
            }
        }
    });
});

console.log('WebSocket server running on ws://localhost:8080');