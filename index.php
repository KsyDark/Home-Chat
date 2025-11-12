<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Retro Chat 2000</title>
<link rel="icon" type="image/png" href="favicon.png">
<style>
body {
  background: url('background.jpg') no-repeat center center fixed;
  background-size: cover;
  font-family: Tahoma, sans-serif;
  color: #000;
}

#chat-container {
  width: 800px;
  background:#f0f0f0;
  border:2px solid #000080;
  box-shadow: 5px 5px 0 #808080;
  border-radius:5px;
  padding:5px;
  position: absolute;
  top: 50px;
  left: 50%;
  transform: translateX(-50%);
}

h1 {
  margin:0;
  background: linear-gradient(#6699ff,#3366cc);
  color:#fff;
  font-size:20px;
  text-align:center;
  padding:5px;
  border:1px solid #000080;
  border-radius:3px;
  cursor: move;
}

#chatbox {
  background: url('chat-bg.jpg') no-repeat center top;
  background-size: cover;
  border: 2px inset #808080;
  height: 600px;
  overflow-y: scroll;
  padding: 5px;
  margin: 5px 0;
  font-family: Tahoma, sans-serif;
  font-size: 14px;
  line-height: 1.4em;
  color: #000080;
}

.user { font-weight:bold; color: #000080; }
.msg { color:#000; }

#chat-form {
  display:flex;
  gap:5px;
  justify-content:center;
  margin-top:5px;
}
#chat-form input[type=text] {
  padding:3px;
  font-size:14px;
  border:1px solid #000080;
  border-radius:3px;
}
#chat-form button {
  background: linear-gradient(#fff,#ccc);
  border:1px solid #000080;
  padding:3px 10px;
  font-weight:bold;
  cursor:pointer;
  border-radius:3px;
}
#chat-form button:hover {
  background: linear-gradient(#ccc,#fff);
}

#smileys button {
  font-size:16px;
  padding:2px 5px;
  margin:0 2px;
  cursor:pointer;
  border:1px solid #000080;
  border-radius:3px;
  background: #fff;
}
#smileys button:hover { background:#cce0ff; }
</style>
</head>
<body>
<div id="chat-container">
<h1 id="chat-header">
  Домашний чат
  <button id="minimize-btn" style="float:right;font-size:12px;padding:0 5px;margin:0;cursor:pointer;border:1px solid #000080;border-radius:3px;background:#fff;">–</button>
</h1>
<div id="chatbox"></div>

<div id="smileys" style="text-align:center; margin-bottom:5px;">
  <button type="button" onclick="addEmoji(':)')">😊</button>
  <button type="button" onclick="addEmoji(':(')">😞</button>
  <button type="button" onclick="addEmoji(':D')">😃</button>
  <button type="button" onclick="addEmoji(';)')">😉</button>
  <button type="button" onclick="addEmoji(':P')">😛</button>
  <button type="button" onclick="addEmoji(':O')">😮</button>
</div>

<form id="chat-form">
  <input type="text" id="username" placeholder="Имя" style="width:120px">
  <input type="text" id="msg" placeholder="Сообщение" style="width:300px">
  <button>Отправить</button>
</form>
</div>

<script>
function parseEmojis(text) {
  const map = { ':)': '😊', ':(': '😞', ':D': '😃', ';)': '😉', ':P': '😛', ':O': '😮' };
  for(let key in map) text = text.replaceAll(key,map[key]);
  return text;
}

function addEmoji(code){
  const msgField = document.getElementById('msg');
  msgField.value += ' ' + code;
  msgField.focus();
}

async function loadChat() {
  const chatbox = document.getElementById('chatbox');
  const r = await fetch('messages.txt?nocache=' + Date.now());
  const t = await r.text();
  const parsed = parseEmojis(t).replace(/\n/g,'<br>');

  const atBottom = chatbox.scrollHeight - chatbox.scrollTop === chatbox.clientHeight;
  chatbox.innerHTML = parsed;
  if(atBottom) chatbox.scrollTop = chatbox.scrollHeight;
}
setInterval(loadChat,500);
loadChat();

(function() {
  const container = document.getElementById('chat-container');
  const header = document.getElementById('chat-header');
  let offsetX = 0, offsetY = 0, isDragging = false;

  const savedPos = JSON.parse(localStorage.getItem('chatPosition') || '{}');
  if (savedPos.left && savedPos.top) {
    container.style.left = savedPos.left + 'px';
    container.style.top = savedPos.top + 'px';
    container.style.transform = 'none';
  }

  header.addEventListener('mousedown', e => {
    isDragging = true;
    offsetX = e.clientX - container.offsetLeft;
    offsetY = e.clientY - container.offsetTop;
    header.style.cursor = 'grabbing';
  });

  document.addEventListener('mousemove', e => {
    if (!isDragging) return;
    container.style.left = e.clientX - offsetX + 'px';
    container.style.top = e.clientY - offsetY + 'px';
  });

  const minimizeBtn = document.getElementById('minimize-btn');
  const chatboxEl = document.getElementById('chatbox');
  const chatForm = document.getElementById('chat-form');
  const smileys = document.getElementById('smileys');

  let minimized = JSON.parse(localStorage.getItem('chatMinimized') || 'false');
  function updateChatDisplay() {
    if(minimized){
      chatboxEl.style.display = 'none';
      chatForm.style.display = 'none';
      smileys.style.display = 'none';
      minimizeBtn.textContent = '+';
    } else {
      chatboxEl.style.display = 'block';
      chatForm.style.display = 'flex';
      smileys.style.display = 'block';
      minimizeBtn.textContent = '–';
    }
  }
  updateChatDisplay();

  minimizeBtn.addEventListener('click', ()=>{
    minimized = !minimized;
    localStorage.setItem('chatMinimized', JSON.stringify(minimized));
    updateChatDisplay();
  });

  document.addEventListener('mouseup', () => {
    if(isDragging){
      localStorage.setItem('chatPosition', JSON.stringify({
        left: parseInt(container.style.left),
        top: parseInt(container.style.top)
      }));
    }
    isDragging = false;
    header.style.cursor = 'move';
  });
})();

let lastMessageCount = 0;
let justSent = false;
const notificationSound = new Audio('ding.mp3');
let blinkInterval;
const originalTitle = document.title;

async function checkNewMessages(){
  const r = await fetch('messages.txt?nocache=' + Date.now());
  const t = await r.text();
  const lines = t.split('\n').filter(l => l.trim() !== '');

  if(lines.length > lastMessageCount){
      if(!justSent){
          notificationSound.play();
          if(document.hidden){
              let showTitle = false;
              clearInterval(blinkInterval);
              blinkInterval = setInterval(()=>{
                  document.title = showTitle ? 'Новое сообщение!' : originalTitle;
                  showTitle = !showTitle;
              }, 500);
          }
      }
  }
  lastMessageCount = lines.length;
  justSent = false;
}

document.addEventListener('visibilitychange', ()=>{
    if(!document.hidden){
        clearInterval(blinkInterval);
        document.title = originalTitle;
    }
});

setInterval(checkNewMessages, 500);

document.getElementById('chat-form').onsubmit = async e => {
    e.preventDefault();
    const u = document.getElementById('username').value.trim();
    const m = document.getElementById('msg').value.trim();
    if(!u||!m) return;

    justSent = true;

    await fetch('write.php',{method:'POST', body:new URLSearchParams({user:u,msg:m})});
    document.getElementById('msg').value='';
    loadChat();
};
</script>
</body>
</html>
