<?php
// includes/footer.php — Frontend Footer
?>
</main><!-- /main-content -->

<!-- Footer -->
<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div>
        <div class="footer-brand">Rise <span>&</span> Shine</div>
        <p class="footer-tagline">L'intelligence beauté à votre service. Découvrez une routine générée par l'IA, conçue uniquement pour vous.</p>
        <div class="social-links">
          <a href="#" class="social-link" aria-label="Instagram">📷</a>
          <a href="#" class="social-link" aria-label="TikTok">🎵</a>
          <a href="#" class="social-link" aria-label="Facebook">📘</a>
        </div>
      </div>

      <!-- Découvrir -->
      <div class="footer-col">
        <h5>Découvrir</h5>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/catalog/products.php">Produits</a></li>
          <li><a href="<?= BASE_URL ?>/catalog/virtual-tryon.php">Try-On IA</a></li>
          <li><a href="<?= BASE_URL ?>/quiz/diagnostic.php">Diagnostic peau</a></li>
          <li><a href="<?= BASE_URL ?>/editorial/blog.php">Blog beauté</a></li>
          <li><a href="<?= BASE_URL ?>/editorial/search.php">Recherche</a></li>
        </ul>
      </div>

      <!-- Mon compte -->
      <div class="footer-col">
        <h5>Mon compte</h5>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/auth/login.php">Connexion</a></li>
          <li><a href="<?= BASE_URL ?>/auth/register.php">Inscription</a></li>
          <li><a href="<?= BASE_URL ?>/profile/profile.php">Mon profil</a></li>
          <li><a href="<?= BASE_URL ?>/profile/favorites.php">Mes favoris</a></li>
        </ul>
      </div>

      <!-- Informations -->
      <div class="footer-col">
        <h5>Informations</h5>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/editorial/about.php">À propos</a></li>
          <li><a href="<?= BASE_URL ?>/editorial/contact.php">Contact</a></li>
          <li><a href="<?= BASE_URL ?>/editorial/legal.php">Mentions légales</a></li>
          <li><a href="<?= BASE_URL ?>/editorial/legal.php#privacy">Confidentialité</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copy">© <?= date('Y') ?> Rise & Shine. Tous droits réservés.</p>
      <div class="social-links">
        <a href="<?= BASE_URL ?>/editorial/legal.php" class="footer-copy">Mentions légales</a>
        <span class="footer-copy">·</span>
        <a href="<?= BASE_URL ?>/editorial/legal.php#privacy" class="footer-copy">Confidentialité</a>
      </div>
    </div>
  </div>
</footer>

<!-- AI Beauty Chat Widget -->
<style>
.chat-fab {
  position: fixed;
  bottom: 28px;
  right: 28px;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-rose), var(--color-gold));
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(209,154,154,0.45);
  transition: all 0.3s ease;
  z-index: 4000;
  color: #fff;
}
.chat-fab:hover { transform: scale(1.1); box-shadow: 0 6px 28px rgba(209,154,154,0.6); }

.chat-window {
  position: fixed;
  bottom: 100px;
  right: 28px;
  width: 340px;
  max-height: 520px;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: 0 20px 60px rgba(74,59,59,0.18);
  display: flex;
  flex-direction: column;
  z-index: 4000;
  overflow: hidden;
  transition: all 0.3s ease;
}
.chat-window.hidden { opacity:0; pointer-events:none; transform: translateY(10px) scale(0.97); }

.chat-header {
  padding: 14px 18px;
  background: linear-gradient(135deg, rgba(209,154,154,0.15), rgba(234,221,205,0.2));
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.chat-header-title {
  font-family: var(--font-serif);
  font-size: 1rem;
  color: var(--color-gold-dark);
  display: flex;
  align-items: center;
  gap: 8px;
}
.chat-header-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--color-rose);
  animation: pulse 1.5s ease-in-out infinite;
}
.chat-close-btn {
  background: none; border: none; cursor: pointer;
  color: var(--color-text-muted); font-size: 1.1rem;
  line-height: 1; padding: 2px 4px;
  transition: color 0.2s;
}
.chat-close-btn:hover { color: var(--color-text); }

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  scroll-behavior: smooth;
}
.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 2px; }

.chat-msg {
  max-width: 85%;
  padding: 9px 13px;
  border-radius: 16px;
  font-size: 0.86rem;
  line-height: 1.55;
  animation: fadeInUp 0.2s ease;
}
.chat-msg.user {
  background: linear-gradient(135deg, var(--color-rose), var(--color-gold));
  color: #fff;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
}
.chat-msg.ai {
  background: var(--color-bg-2);
  color: var(--color-text);
  align-self: flex-start;
  border: 1px solid var(--color-border);
  border-bottom-left-radius: 4px;
}
.chat-msg.typing {
  color: var(--color-text-subtle);
  font-style: italic;
}

.chat-input-bar {
  padding: 12px 14px;
  border-top: 1px solid var(--color-border);
  display: flex;
  gap: 8px;
  align-items: center;
  background: var(--color-bg);
}
.chat-input {
  flex: 1;
  padding: 9px 13px;
  background: var(--color-bg-2);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-full);
  font-size: 0.88rem;
  font-family: var(--font-sans);
  color: var(--color-text);
  outline: none;
  transition: border-color 0.2s;
}
.chat-input:focus { border-color: var(--color-rose); }
.chat-input::placeholder { color: var(--color-text-subtle); }

.chat-send-btn {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-rose), var(--color-gold));
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #fff;
  transition: all 0.2s;
  flex-shrink: 0;
}
.chat-send-btn:hover { transform: scale(1.1); }
.chat-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

@keyframes fadeInUp {
  from { opacity:0; transform:translateY(6px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>

<!-- FAB Button -->
<button class="chat-fab" id="chatFab" onclick="toggleChat()" aria-label="Ouvrir le conseiller beauté IA" title="Conseillère Beauté IA">
  💄
</button>

<!-- Chat Window -->
<div class="chat-window hidden" id="chatWindow" role="dialog" aria-label="Conseillère Beauté IA">
  <div class="chat-header">
    <div class="chat-header-title">
      <div class="chat-header-dot"></div>
      ✨ Conseillère Beauté IA
    </div>
    <button class="chat-close-btn" onclick="toggleChat()" aria-label="Fermer">✕</button>
  </div>
  <div class="chat-messages" id="chatMessages">
    <div class="chat-msg ai">
      Bonjour ! 💄 Je suis votre conseillère beauté IA. Posez-moi n'importe quelle question sur votre peau, votre maquillage ou vos soins — je suis là pour vous guider !
    </div>
  </div>
  <div class="chat-input-bar">
    <input type="text" class="chat-input" id="chatInput"
           placeholder="Posez votre question beauté…"
           onkeydown="if(event.key==='Enter')sendChat()"
           maxlength="400"
           autocomplete="off">
    <button class="chat-send-btn" id="chatSendBtn" onclick="sendChat()" aria-label="Envoyer">→</button>
  </div>
</div>

<script>
// ─── Chat State ────────────────────────────────────────────────
const chatHistory = [];

function toggleChat() {
  const win = document.getElementById('chatWindow');
  win.classList.toggle('hidden');
  if (!win.classList.contains('hidden')) {
    document.getElementById('chatInput').focus();
    scrollChatBottom();
  }
}

function scrollChatBottom() {
  const msgs = document.getElementById('chatMessages');
  msgs.scrollTop = msgs.scrollHeight;
}

function appendMsg(text, role) {
  const msgs = document.getElementById('chatMessages');
  const div  = document.createElement('div');
  div.className = `chat-msg ${role}`;
  div.textContent = text;
  msgs.appendChild(div);
  scrollChatBottom();
  return div;
}

async function sendChat() {
  const input   = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSendBtn');
  const message = input.value.trim();
  if (!message) return;

  // Show user message
  appendMsg(message, 'user');
  chatHistory.push({ role: 'user', content: message });
  input.value = '';
  sendBtn.disabled = true;

  // Typing indicator
  const typing = appendMsg('…', 'ai typing');

  try {
    const res  = await fetch((window.BASE_URL || '') + '/api/ai-chat.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ message, history: chatHistory.slice(-6) }),
    });
    const data = await res.json();

    typing.remove();

    const reply = data.reply || data.error || "Je suis temporairement indisponible. Réessayez dans un instant.";
    appendMsg(reply, 'ai');
    chatHistory.push({ role: 'assistant', content: reply });

  } catch (e) {
    typing.remove();
    appendMsg("Connexion impossible. Vérifiez votre réseau.", 'ai');
  }

  sendBtn.disabled = false;
  input.focus();
}
</script>

<!-- Shared Auth Modal -->
<?php include_once __DIR__ . '/auth-modal.php'; ?>

<!-- Scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
