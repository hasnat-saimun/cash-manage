// ...example logic...
$model = env('AI_MODEL', 'qwen3-4b-instant-apply-ft');
$available = checkModelAvailable($model); // implement listing/check call to provider
if (! $available) {
    $model = env('AI_FALLBACK_MODEL', 'gpt-4o-mini');
}
$response = callAiModel($model, $payload);
