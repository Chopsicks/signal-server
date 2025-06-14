const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

wss.on('connection', (socket) => {
  console.log('✅ Client connected');

  socket.on('message', (message) => {
    console.log('📩 Message received:', message);

    // Пример: рассылаем сообщение обратно всем
    wss.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message);
      }
    });
  });

  socket.on('close', () => {
    console.log('❌ Client disconnected');
  });
});

app.get('/', (req, res) => {
  res.send('WebSocket Server is running');
});

const PORT = process.env.PORT || 10000;
server.listen(PORT, () => {
  console.log(`🚀 Server running on port ${PORT}`);
});
