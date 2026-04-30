import json
from pathlib import Path
from graphify.extract import extract

data = json.loads(Path('graphify-out/.graphify_detect.json').read_text())
all_code = data.get('files', {}).get('code', [])
filtered_code = [f for f in all_code if 'vendor\\' not in f and 'writable\\' not in f and '.git\\' not in f]

if filtered_code:
    result = extract([Path(f) for f in filtered_code])
    with open('graphify-out/.graphify_ast.json', 'w', encoding='utf-8') as f:
        json.dump(result, f, indent=2)
    print(f"AST: {len(result['nodes'])} nodes, {len(result['edges'])} edges")
else:
    with open('graphify-out/.graphify_ast.json', 'w', encoding='utf-8') as f:
        json.dump({'nodes':[],'edges':[],'input_tokens':0,'output_tokens':0}, f)
    print('No code files - skipping AST extraction')
