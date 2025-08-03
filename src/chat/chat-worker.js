const chats = {};
const ports = [];

function pollChat(chatId) {
  const chat = chats[chatId];
  if (!chat) return;

  fetch(chat.route, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'get',
      local: chat.local,
      remote: chat.remote,
      version: chat.version
    })
  })
    .then(res => res.json())
    .then(d => {
      let newMsgs = 0;
      if (Array.isArray(d.data)) {
        // Count new messages by comparing version
        // If version increases, assume new messages
        if (chat.lastVersion !== undefined && d.version > chat.lastVersion) {
          newMsgs = 1;
        }
        chat.lastVersion = d.version;
      }
      ports.forEach(port => {
        port.postMessage({ ...d, chatId });
      });

      // stepped fallback logic
      if (newMsgs > 0) {
        chat.pollStep = 0;
        setTimeout(() => pollChat(chatId), 2000);
      } else {
        chat.pollStep = (chat.pollStep || 0) + 1;
        let nextPoll;
        if (chat.pollStep >= 1 && chat.pollStep <= 8) {
          nextPoll = (chat.pollStep + 2) * 1000; // 3s, 4s, ..., 10s
        } else {
          nextPoll = 10000; // cap at 10s
        }
        setTimeout(() => pollChat(chatId), nextPoll);
      }
    });
}

onconnect = function (e) {
  const port = e.ports[0];
  ports.push(port);

  port.onmessage = function (event) {

    const msg = event.data;
    if (msg.type === 'register') {

      chats[msg.chatId] = {
        route: msg.route,
        local: msg.local,
        remote: msg.remote,
        version: msg.version
      };
      pollChat(msg.chatId);
    } else if (msg.type === 'update') {

      chats[msg.chatId] = {
        ...chats[msg.chatId],
        local: msg.local,
        remote: msg.remote,
        version: msg.version
      };
      pollChat(msg.chatId);
    } else if (msg.type === 'unregister') {

      delete chats[msg.chatId];
    }
  };
};
