import json
from pathlib import Path
from datetime import datetime, timezone
from graphify.detect import save_manifest

# Save manifest for --update
detect = json.loads(Path('graphify_output/.graphify_detect.json').read_text(encoding='utf-8'))
save_manifest(detect['files'], 'graphify_output/manifest.json')

# Update cumulative cost tracker
extract = json.loads(Path('graphify_output/.graphify_extract.json').read_text(encoding='utf-8'))
input_tok = extract.get('input_tokens', 0)
output_tok = extract.get('output_tokens', 0)

cost_path = Path('graphify_output/cost.json')
if cost_path.exists():
    cost = json.loads(cost_path.read_text(encoding='utf-8'))
else:
    cost = {'runs': [], 'total_input_tokens': 0, 'total_output_tokens': 0}

cost['runs'].append({
    'date': datetime.now(timezone.utc).isoformat(),
    'input_tokens': input_tok,
    'output_tokens': output_tok,
    'files': detect.get('total_files', 0),
})
cost['total_input_tokens'] += input_tok
cost['total_output_tokens'] += output_tok
cost_path.write_text(json.dumps(cost, indent=2), encoding='utf-8')

print(f'This run: {input_tok:,} input tokens, {output_tok:,} output tokens')
print(f'All time: {cost["total_input_tokens"]:,} input, {cost["total_output_tokens"]:,} output ({len(cost["runs"])} runs)')

# Cleanup temporary graphify files
temp_files = [
    'graphify_output/.graphify_detect.json',
    'graphify_output/.graphify_extract.json',
    'graphify_output/.graphify_ast.json',
    'graphify_output/.graphify_semantic.json',
    'graphify_output/.graphify_analysis.json',
    'graphify_output/.graphify_labels.json',
    'graphify_output/.graphify_chunk_01.json',
    'graphify_output/.graphify_cached.json',
    'graphify_output/.needs_update',
]
for f in temp_files:
    p = Path(f)
    if p.exists():
        p.unlink()

# Cleanup temporary helper scripts
helper_scripts = [
    'graphify_output/step1_2.py',
    'graphify_output/step3_b0.py',
    'graphify_output/step3_ast.py',
    'graphify_output/step3_merge_semantic.py',
    'graphify_output/step3_merge_all.py',
    'graphify_output/step4.py',
    'graphify_output/step5_label.py',
    'graphify_output/step6_viz.py',
    'graphify_output/step8_benchmark.py',
]
for f in helper_scripts:
    p = Path(f)
    if p.exists():
        p.unlink()
