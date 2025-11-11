<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Retro Chat 2000</title>
<link rel="icon" type="image/png" href="favicon.png">
<style>
/* Фоновое изображение */
body {
  background: url('background.jpg') no-repeat center center fixed;
  background-size: cover;
  font-family: Tahoma, sans-serif;
  color: #000;
}

/* Окно чата */
#chat-container {
  width: 800px;
  background:#f0f0f0;
  border:2px solid #000080;
  box-shadow: 5px 5px 0 #808080;
  border-radius:5px;
  padding:5px;
  position: absolute; /* чтобы двигалось */
  top: 50px;
  left: 50%;
  transform: translateX(-50%);
}

/* Заголовок ICQ */
h1 {
  margin:0;
  background: linear-gradient(#6699ff,#3366cc);
  color:#fff;
  font-size:20px;
  text-align:center;
  padding:5px;
  border:1px solid #000080;
  border-radius:3px;
  cursor: move; /* указатель “перемещение” */
}

/* Окно сообщений с фоном ICQ/2000-х */
#chatbox {
  background: url('chat-bg.jpg') no-repeat center top; /* твоя картинка */
  background-size: cover;   /* растянуть на весь блок */
  border: 2px inset #808080; /* стиль рамки под старый ICQ */
  height: 600px;
  overflow-y: scroll;
  padding: 5px;
  margin: 5px 0;
  font-family: Tahoma, sans-serif;
  font-size: 14px;
  line-height: 1.4em;
  color: #000080;           /* цвет текста как в ICQ */
 // box-shadow: inset 2px 2px 0 #fff, inset -2px -2px 0 #000; /* псевдо-3D эффект */
}

/* Пользователь и сообщение */
.user { font-weight:bold; color: #000080; }
.msg { color:#000; }

/* Форма */
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

/* Смайлы */
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
  <button id="minimize-btn" style="
    float:right;
    font-size:12px;
    padding:0 5px;
    margin:0;
    cursor:pointer;
    border:1px solid #000080;
    border-radius:3px;
    background:#fff;
  ">–</button>
</h1>
<div id="chatbox"></div>

<!-- Панель смайлов -->
<div id="smileys" style="text-align:center; margin-bottom:5px;">
  <button type="button" onclick="addEmoji(':)')">😊</button>
  <button type="button" onclick="addEmoji(':(')">😞</button>
  <button type="button" onclick="addEmoji(':D')">😃</button>
  <button type="button" onclick="addEmoji(';)')">😉</button>
  <button type="button" onclick="addEmoji(':P')">😛</button>
  <button type="button" onclick="addEmoji(':O')">😮</button>
</div>

<form id="chat-form">
  <input type="text" id="username" placeholder="Имя" style="width:80px">
  <input type="text" id="msg" placeholder="Сообщение" style="width:300px">
  <button>Отправить</button>
</form>
</div>

<script>
// 💬 Смайлы
function parseEmojis(text) {
  const map = {
    ':)': '😊',
    ':(': '😞',
    ':D': '😃',
    ';)': '😉',
    ':P': '😛',
    ':O': '😮'
  };
  for(let key in map) text = text.replaceAll(key,map[key]);
  return text;
}

// Прогреть кеш при первой загрузке
fetch('messages.txt', {cache: "no-store"}).then(r => r.text());

// 😄 Добавление смайлов
function addEmoji(code){
  const msgField = document.getElementById('msg');
  msgField.value += ' ' + code;
  msgField.focus();
}

// 🔁 Загрузка сообщений
async function loadChat() {
  const chatbox = document.getElementById('chatbox');
  const r = await fetch('messages.txt');
  const t = await r.text();
  const parsed = parseEmojis(t).replace(/\n/g,'<br>');

  // Проверяем, скролл был внизу
  const atBottom = chatbox.scrollHeight - chatbox.scrollTop === chatbox.clientHeight;

  chatbox.innerHTML = parsed;

  // Скроллим вниз только если был внизу
  if(atBottom) {
    chatbox.scrollTop = chatbox.scrollHeight;
  }
}
setInterval(loadChat,500);
loadChat();

// 📤 Отправка
document.getElementById('chat-form').onsubmit = async e => {
  e.preventDefault();
  const u = document.getElementById('username').value.trim();
  const m = document.getElementById('msg').value.trim();
  if(!u||!m) return;
  await fetch('write.php',{method:'POST', body:new URLSearchParams({user:u,msg:m})});
  document.getElementById('msg').value='';
  loadChat();
};

// 🖱️ Перетаскивание с сохранением позиции
(function() {
  const container = document.getElementById('chat-container');
  const header = document.getElementById('chat-header');
  let offsetX = 0, offsetY = 0, isDragging = false;

  // если ранее сохраняли — восстановить позицию
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

// Свернуть/развернуть чат
const minimizeBtn = document.getElementById('minimize-btn');
const chatbox = document.getElementById('chatbox');
const chatForm = document.getElementById('chat-form');
const smileys = document.getElementById('smileys');

let minimized = JSON.parse(localStorage.getItem('chatMinimized') || 'false');
function updateChatDisplay() {
  if(minimized) {
    chatbox.style.display = 'none';
    chatForm.style.display = 'none';
    smileys.style.display = 'none';
    minimizeBtn.textContent = '+';
  } else {
    chatbox.style.display = 'block';
    chatForm.style.display = 'flex';
    smileys.style.display = 'block';
    minimizeBtn.textContent = '–';
  }
}
updateChatDisplay();

minimizeBtn.addEventListener('click', () => {
  minimized = !minimized;
  localStorage.setItem('chatMinimized', JSON.stringify(minimized));
  updateChatDisplay();
});

  document.addEventListener('mouseup', () => {
    if (isDragging) {
      localStorage.setItem('chatPosition', JSON.stringify({
        left: parseInt(container.style.left),
        top: parseInt(container.style.top)
      }));
    }
    isDragging = false;
    header.style.cursor = 'move';
  });
})();

// 🔔 Уведомления о новых сообщениях
let lastMessageCount = 0;
let justSent = false;
const notificationSound = new Audio('ding.mp3');
let blinkInterval;
const originalTitle = document.title;

async function checkNewMessages() {
    const r = await fetch('messages.txt', {cache:"no-store"});
    const t = await r.text();
    const lines = t.split('\n').filter(l => l.trim() !== '');

    if(lines.length > lastMessageCount){
        if(!justSent){
            // звук
            notificationSound.play();

            // мигание заголовка, если вкладка неактивна
            if(document.hidden){
                let showTitle = false;
                clearInterval(blinkInterval);
                blinkInterval = setInterval(() => {
                    document.title = showTitle ? 'Новое сообщение!' : originalTitle;
                    showTitle = !showTitle;
                }, 500);
            }
        }
    }

    lastMessageCount = lines.length;
    justSent = false; // после проверки сбрасываем флаг
}

// Останавливаем мигание, когда пользователь возвращается на вкладку
document.addEventListener('visibilitychange', ()=>{
    if(!document.hidden){
        clearInterval(blinkInterval);
        document.title = originalTitle;
    }
});

// Отправка сообщения
document.getElementById('chat-form').onsubmit = async e => {
    e.preventDefault();
    const u = document.getElementById('username').value.trim();
    const m = document.getElementById('msg').value.trim();
    if(!u||!m) return;

    justSent = true; // ставим флаг перед отправкой

    await fetch('write.php',{method:'POST', body:new URLSearchParams({user:u,msg:m})});
    document.getElementById('msg').value='';
    loadChat();
};

// Проверка новых сообщений каждые 500 мс
setInterval(checkNewMessages, 500);

</script>
</body>
</html>
