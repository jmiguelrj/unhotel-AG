(function(){
  const elTable = document.getElementById('unfd-table');
  const elWin = document.getElementById('unfd-window');
  const elSearch = document.getElementById('unfd-search');
  const elPrev = document.getElementById('unfd-prev');
  const elNext = document.getElementById('unfd-next');
  const elPage = document.getElementById('unfd-page');

  let page = 1, limit = UNFD.limit || 50, windowKey = UNFD.window || 'today', search = '';

  elWin.value = windowKey;

  elWin.addEventListener('change', () => { windowKey = elWin.value; page = 1; load(); });
  elSearch.addEventListener('input', debounce(() => { search = elSearch.value.trim(); page = 1; load(); }, 350));
  elPrev.addEventListener('click', () => { if (page>1){ page--; load(); } });
  elNext.addEventListener('click', () => { page++; load(); });

  function load(){
    elPage.textContent = page;
    fetch(`${UNFD.rest}arrivals?window=${encodeURIComponent(windowKey)}&page=${page}&limit=${limit}&search=${encodeURIComponent(search)}`, {
      headers: { 'X-WP-Nonce': UNFD.nonce }
    }).then(r=>r.json()).then(draw).catch(err=>{
      elTable.innerHTML = `<div class="unfd-error">Error loading: ${err}</div>`;
    });
  }

  function draw(data){
    const { rows, total, lists } = data;
    const totalPages = Math.max(1, Math.ceil(total/limit));
    if (page>totalPages){ page=totalPages; elPage.textContent=page; }

    const rs = lists.reg_status || [];
    const rc = lists.receiver || [];

    const header = `<div class="unfd-row unfd-head">
      <div>Apt</div><div>No</div><div>Nome</div><div>Check-in</div><div>Chegada</div><div>Cadastro</div><div>Rec</div><div>Obs</div><div></div>
    </div>`;
    const rowsHtml = (rows||[]).map(r => {
      const arrival = r.arrival_time ? r.arrival_time.slice(0,5) : '';
      return `<div class="unfd-row" data-id="${r.idorderroom}">
        <div>${r.apartment||''}</div>
        <div>${r.reservation_no||''}</div>
        <div>${r.guest_first||''}</div>
        <div>${fmtDate(r.checkin_ts)}</div>
        <div><input type="time" value="${arrival}" class="unfd-arrival"/></div>
        <div>${select(rs, r.reg_status, 'unfd-reg')}</div>
        <div>${select(rc, r.receiver, 'unfd-recv')}</div>
        <div><textarea class="unfd-obs">${r.obs?escapeHtml(r.obs):''}</textarea></div>
        <div><button class="unfd-save">Save</button></div>
      </div>`;
    }).join('');

    elTable.innerHTML = header + rowsHtml;

    elTable.querySelectorAll('.unfd-save').forEach(btn => {
      btn.addEventListener('click', () => {
        const row = btn.closest('.unfd-row');
        const id = row.getAttribute('data-id');
        const payload = {
          arrival_time: row.querySelector('.unfd-arrival').value || null,
          reg_status: row.querySelector('.unfd-reg').value || null,
          receiver: row.querySelector('.unfd-recv').value || null,
          obs: row.querySelector('.unfd-obs').value || null,
        };
        btn.disabled = true;
        fetch(`${UNFD.rest}arrivals/${id}`, {
          method:'PATCH',
          headers:{ 'Content-Type':'application/json','X-WP-Nonce':UNFD.nonce },
          body: JSON.stringify(payload)
        }).then(r=>r.json()).then(() => {
          btn.disabled=false;
          row.classList.add('unfd-ok'); setTimeout(()=>row.classList.remove('unfd-ok'), 800);
        }).catch(()=>{ btn.disabled=false; row.classList.add('unfd-err'); setTimeout(()=>row.classList.remove('unfd-err'), 1200); });
      });
    });
  }

  function fmtDate(ts){
    if (!ts) return '';
    const d = new Date(ts*1000);
    const dd = String(d.getDate()).padStart(2,'0');
    const mm = String(d.getMonth()+1).padStart(2,'0');
    return `${dd}/${mm}`;
  }
  function select(opts, value, cls){
    const o = ['<select class="'+cls+'">']
      .concat(opts.map(x => `<option ${x===value?'selected':''}>${x}</option>`))
      .concat(['</select>'])
      .join('');
    return o;
  }
  function debounce(fn,ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
  function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  load();
})();