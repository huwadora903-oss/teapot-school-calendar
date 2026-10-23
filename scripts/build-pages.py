"""Build a browser-storage demo without changing the PHP/MariaDB edition."""
from pathlib import Path
import re

root = Path(__file__).resolve().parent.parent
output = root / 'pages'
output.mkdir(exist_ok=True)
source = (root / 'public/index.html').read_text(encoding='utf-8')
source = source.replace('<head>', '<head>\n<script src="browser-storage.js"></script>', 1)
source = source.replace('events: calEventsApiUrl,', '''events: function(info, success, failure) {
                    fetch(calEventsApiUrl).then(function(response) {
                        if (!response.ok) throw new Error('体験データを読み込めませんでした');
                        return response.json();
                    }).then(success).catch(failure);
                },''', 1)
banner = '''
<style>
.demo-notice{box-sizing:border-box;max-width:1120px;margin:16px auto;padding:14px 18px;border:1px solid #bdd6d0;border-radius:14px;background:#eff8f5;color:#174a41;font:14px/1.65 system-ui,sans-serif;display:flex;gap:16px;align-items:center;justify-content:space-between}
.demo-notice strong{display:block;font-size:16px}.demo-notice p{margin:3px 0 0}.demo-notice button{flex-shrink:0;background:white;color:#174a41;border:1px solid #87aaa1;border-radius:8px;padding:9px 12px;cursor:pointer;font:inherit}.demo-notice button:focus-visible{outline:3px solid #146b56;outline-offset:3px}
@media(max-width:600px){.demo-notice{margin:8px;align-items:flex-start;flex-direction:column;gap:8px}}
@media(prefers-color-scheme:dark){.demo-notice{background:#162b26;color:#d8efe8;border-color:#47685e}.demo-notice button{background:#233e35;color:#d8efe8;border-color:#729b8d}}
</style>
<aside class="demo-notice" aria-label="体験版について"><div><strong>学校カレンダー · 体験版</strong><p>予定はこのブラウザーだけに保存されます。他の人や学校の共有DBとは同期しません。架空の情報でお試しください。</p></div><button id="demo-reset" type="button">体験データをリセット</button></aside>
'''
source = source.replace('<body>', '<body>'+banner, 1)
(output / 'index.html').write_text(source,encoding='utf-8')
(output / 'browser-storage.js').write_text((root / 'demo/browser-storage.js').read_text(encoding='utf-8'),encoding='utf-8')
(output / '.nojekyll').write_text('',encoding='utf-8')
print('Built pages/index.html and browser-storage.js')
