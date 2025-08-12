const WebSocket = require('ws');
const http = require('http');

// Configuration
const PORT = 8080;
const HOST = '0.0.0.0'; // Listen on all interfaces - accessible from any IP

// Create HTTP server
const server = http.createServer();

// Create WebSocket server
const wss = new WebSocket.Server({ server });

// Store active connections
const streamers = new Map(); // adminId -> WebSocket
const viewers = new Map();   // viewerId -> WebSocket

console.log(`🚀 WebSocket server starting on ws://${HOST}:${PORT}`);

wss.on('connection', (ws, req) => {
    console.log(`🔌 New connection from ${req.socket.remoteAddress}`);
    
    let connectionType = null;
    let connectionId = null;

    ws.on('message', async (message) => {
        try {
            const data = JSON.parse(message);
            console.log(`📩 Received: ${data.type}`);

            switch (data.type) {
                case 'streamer':
                    // Streamer connecting
                    connectionType = 'streamer';
                    connectionId = data.adminId;
                    streamers.set(connectionId, ws);
                    console.log(`🎥 Streamer connected: ${connectionId}`);
                    
                    ws.send(JSON.stringify({
                        type: 'streamerConnected',
                        adminId: connectionId
                    }));
                    break;

                case 'viewer':
                    // Viewer connecting
                    connectionType = 'viewer';
                    connectionId = data.viewerId;
                    viewers.set(connectionId, ws);
                    console.log(`👁️ Viewer connected: ${connectionId} looking for streamer: ${data.adminId}`);
                    
                    // Check if requested streamer is available
                    const targetStreamer = streamers.get(data.adminId);
                    if (targetStreamer && targetStreamer.readyState === WebSocket.OPEN) {
                        // Forward viewer request to streamer
                        targetStreamer.send(JSON.stringify({
                            type: 'viewerRequest',
                            viewerId: connectionId
                        }));
                    } else {
                        // Streamer not available
                        ws.send(JSON.stringify({
                            type: 'streamerUnavailable',
                            adminId: data.adminId
                        }));
                    }
                    break;

                case 'offer':
                    // Streamer sending offer to viewer
                    const targetViewer = viewers.get(data.viewerId);
                    if (targetViewer && targetViewer.readyState === WebSocket.OPEN) {
                        targetViewer.send(JSON.stringify({
                            type: 'offer',
                            offer: data.offer,
                            adminId: data.adminId
                        }));
                    }
                    break;

                case 'answer':
                    // Viewer sending answer to streamer
                    const targetStreamerForAnswer = streamers.get(data.adminId);
                    if (targetStreamerForAnswer && targetStreamerForAnswer.readyState === WebSocket.OPEN) {
                        targetStreamerForAnswer.send(JSON.stringify({
                            type: 'answer',
                            answer: data.answer,
                            viewerId: data.viewerId
                        }));
                    }
                    break;

                case 'candidate':
                    // ICE candidate exchange
                    if (data.target === 'streamer') {
                        const targetStreamerForCandidate = streamers.get(data.adminId);
                        if (targetStreamerForCandidate && targetStreamerForCandidate.readyState === WebSocket.OPEN) {
                            targetStreamerForCandidate.send(JSON.stringify({
                                type: 'candidate',
                                candidate: data.candidate,
                                viewerId: data.viewerId
                            }));
                        }
                    } else {
                        const targetViewerForCandidate = viewers.get(data.viewerId);
                        if (targetViewerForCandidate && targetViewerForCandidate.readyState === WebSocket.OPEN) {
                            targetViewerForCandidate.send(JSON.stringify({
                                type: 'candidate',
                                candidate: data.candidate,
                                adminId: data.adminId
                            }));
                        }
                    }
                    break;

                default:
                    console.log(`❓ Unknown message type: ${data.type}`);
            }
        } catch (error) {
            console.error(`❌ Error processing message:`, error);
        }
    });

    ws.on('close', () => {
        console.log(`🔌 Connection closed: ${connectionType} ${connectionId}`);
        
        if (connectionType === 'streamer' && connectionId) {
            streamers.delete(connectionId);
            // Notify all viewers that streamer is offline
            viewers.forEach((viewerWs, viewerId) => {
                if (viewerWs.readyState === WebSocket.OPEN) {
                    viewerWs.send(JSON.stringify({
                        type: 'streamerDisconnected',
                        adminId: connectionId
                    }));
                }
            });
        } else if (connectionType === 'viewer' && connectionId) {
            viewers.delete(connectionId);
        }
    });

    ws.on('error', (error) => {
        console.error(`❌ WebSocket error:`, error);
    });
});

server.listen(PORT, HOST, () => {
    console.log(`✅ WebSocket server running on ws://${HOST}:${PORT}`);
    console.log(`📊 Active streamers: ${streamers.size}, Active viewers: ${viewers.size}`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down WebSocket server...');
    wss.close(() => {
        server.close(() => {
            console.log('✅ Server closed');
            process.exit(0);
        });
    });
}); 