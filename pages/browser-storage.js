/* GitHub Pages demo adapter. School PHP APIs are never contacted. */
(() => {
  'use strict';
  const key = 'school-calendar-demo-v1';
  const realFetch = window.fetch.bind(window);
  const sample = () => {
    const day = new Date();
    day.setDate(day.getDate() + 3);
    const date = offset => {
      const d = new Date(day); d.setDate(d.getDate() + offset);
      return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    };
    return [
      {id:'1',title:'情報I レポート',start:date(0),end:null,allDay:true,color:'#f59e0b',extendedProps:{event_type:'assignment',time_mode:'period',period:3,memo:'【架空の予定】授業のはじめに提出。PDF形式。'}},
      {id:'2',title:'数学 小テスト',start:date(1),end:null,allDay:true,color:'#ef4444',extendedProps:{event_type:'test',time_mode:'period',period:1,memo:'【架空の予定】教科書20〜25ページ。筆記用具を準備。'}},
      {id:'3',title:'学校行事の準備',start:date(4),end:null,allDay:true,color:'#3b82f6',extendedProps:{event_type:'event',time_mode:'allday',period:null,memo:null}}
    ];
  };
  function read() {
    const saved = localStorage.getItem(key);
    if (saved === null) { const events = sample(); write(events); return events; }
    const events = JSON.parse(saved);
    if (!Array.isArray(events)) throw new Error('保存データを読み込めません。体験データのリセットをお試しください。');
    return events;
  }
  function write(events) { localStorage.setItem(key, JSON.stringify(events)); }
  function result(body, status=200) {
    return new Response(JSON.stringify(body), {status,headers:{'Content-Type':'application/json'}});
  }
  function date(value) {
    if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(value)) throw new Error('日付または時刻が不正です。');
    const d = new Date(value.replace(' ', 'T'));
    const parts = value.match(/\d+/g).map(Number);
    if (!Number.isFinite(d.getTime()) || d.getFullYear()!==parts[0] || d.getMonth()+1!==parts[1] || d.getDate()!==parts[2] || d.getHours()!==parts[3] || d.getMinutes()!==parts[4] || d.getSeconds()!==parts[5]) throw new Error('日付または時刻が不正です。');
    return value;
  }
  window.fetch = async (input, options={}) => {
    const url = new URL(typeof input==='string' ? input : input.url, location.href);
    const endpoint = url.pathname.split('/').pop();
    if (url.origin!==location.origin || !url.pathname.includes('/api/') || !['get_events.php','add_event.php','update_event.php','delete_event.php'].includes(endpoint)) return realFetch(input, options);
    try {
      const events = read();
      if (endpoint==='get_events.php') return result(events);
      if ((options.method||'GET').toUpperCase()!=='POST') return result({success:false,error:'POSTを使用してください。'},405);
      const data = Object.fromEntries(new URLSearchParams(options.body));
      const index = events.findIndex(e=>e.id===data.id);
      if (endpoint!=='add_event.php' && index<0) return result({success:false,error:'予定が見つかりません。'},404);
      if (endpoint==='delete_event.php') { events.splice(index,1); write(events); return result({success:true}); }
      const previous = index<0 ? null : events[index];
      const props = previous?.extendedProps || {};
      const title = (data.title ?? previous?.title ?? '').trim();
      if (!title || [...title].length>255) throw new Error('タイトルは1〜255文字で入力してください。');
      const mode = data.time_mode ?? props.time_mode ?? 'allday';
      const type = data.event_type ?? props.event_type ?? 'other';
      if (!['clock','allday','period'].includes(mode) || !['event','assignment','test','other'].includes(type)) throw new Error('予定の種類または時間指定が不正です。');
      let start = date(data.start ?? (previous?.start.length===10 ? previous.start+' 00:00:00' : previous?.start) ?? '');
      const rawEnd = data.end ?? previous?.end;
      let end = rawEnd ? date(rawEnd) : null;
      if (mode==='clock' && end && end<=start) throw new Error('終了時刻は開始時刻より後に設定してください。');
      const period = mode==='period' ? Number(data.period ?? props.period) : null;
      if (mode==='period' && (!Number.isInteger(period) || period<1 || period>6)) throw new Error('時限は1〜6限で指定してください。');
      const color = data.color ?? previous?.color ?? '#3788d8';
      if (!/^#[0-9a-f]{6}$/i.test(color)) throw new Error('予定の色が不正です。');
      const memo = data.memo ?? props.memo ?? '';
      if ([...memo].length>2000) throw new Error('備考は2000文字以内で入力してください。');
      if (mode!=='clock') {start=start.slice(0,10);end=null;}
      const id = previous?.id ?? String(Math.max(0,...events.map(e=>Number(e.id)||0))+1);
      const event = {id,title,start,end,allDay:mode!=='clock',color,extendedProps:{event_type:type,time_mode:mode,period,memo:memo||null}};
      if (previous) events[index]=event; else events.push(event);
      write(events); return result({success:true,id});
    } catch(error) {
      return result({success:false,error:error instanceof DOMException ? 'ブラウザーに保存できません。保存の許可や空き容量を確認してください。' : error.message},422);
    }
  };
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('demo-reset').addEventListener('click', () => {
      if (confirm('このブラウザーに保存した体験用の予定を消して、架空の予定3件に戻しますか？')) {
        try {write(sample());location.reload();} catch (_) {alert('ブラウザーに保存できません。保存の許可を確認してください。');}
      }
    });
  });
})();
