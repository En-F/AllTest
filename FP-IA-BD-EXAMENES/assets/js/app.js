(() => {
  const menuBtn = document.getElementById('menuBtn');
  const sidebar = document.getElementById('sidebar');
  if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  const examForm = document.getElementById('examForm');
  if (examForm) {
    const start = Date.now();
    const timer = document.getElementById('timer');
    const durationInput = document.getElementById('durationSeconds');

    const tick = () => {
      const sec = Math.max(0, Math.floor((Date.now() - start) / 1000));
      if (durationInput) durationInput.value = String(sec);
      const m = String(Math.floor(sec / 60)).padStart(2, '0');
      const s = String(sec % 60).padStart(2, '0');
      if (timer) timer.textContent = `${m}:${s}`;
    };
    tick();
    const t = setInterval(tick, 1000);

    examForm.addEventListener('change', e => {
      const input = e.target;
      if (!(input instanceof HTMLInputElement) || input.type !== 'radio') return;
      const name = input.name;
      const match = name.match(/answers\[(\d+)\]/);
      if (!match) return;
      const nav = document.querySelector(`[data-nav-for="${match[1]}"]`);
      if (nav) nav.classList.add('answered');
    });

    examForm.addEventListener('submit', () => {
      tick();
      clearInterval(t);
    });
  }

  const builder = document.getElementById('questionBuilder');
  const addQuestion = document.getElementById('addQuestion');
  const questionCount = document.getElementById('questionCount');

  if (builder && addQuestion && questionCount) {
    let count = 0;
    const add = () => {
      if (count >= 50) return;
      count += 1;
      questionCount.value = String(count);
      const wrapper = document.createElement('section');
      wrapper.className = 'question-editor';
      wrapper.innerHTML = `
        <h4>Pregunta ${count}</h4>
        <label>Enunciado<textarea name="q${count}_text" rows="3" required></textarea></label>
        <div class="option-grid">
          <label>Opción A<input name="q${count}_a" required></label>
          <label>Opción B<input name="q${count}_b" required></label>
          <label>Opción C<input name="q${count}_c" required></label>
          <label>Opción D<input name="q${count}_d" required></label>
        </div>
        <label>Respuesta correcta
          <select name="q${count}_correct" required>
            <option value="A">A</option><option value="B">B</option>
            <option value="C">C</option><option value="D">D</option>
          </select>
        </label>
        <label>Explicación<textarea name="q${count}_explanation" rows="2"></textarea></label>`;
      builder.appendChild(wrapper);
    };
    add();
    addQuestion.addEventListener('click', add);
  }
})();
