<?php
function get_leads_storage_path(): string
{
    return dirname(__DIR__) . '/data/leads.json';
}

function ensure_leads_storage(): void
{
    $storagePath = get_leads_storage_path();
    $storageDir = dirname($storagePath);

    if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
        throw new RuntimeException('Unable to create leads storage directory.');
    }

    if (!file_exists($storagePath)) {
        file_put_contents($storagePath, '[]', LOCK_EX);
    }
}

function load_leads(): array
{
    ensure_leads_storage();

    $contents = file_get_contents(get_leads_storage_path());
    $data = json_decode($contents, true);

    return is_array($data) ? $data : [];
}

function save_lead(array $lead): void
{
    ensure_leads_storage();

    $leads = load_leads();
    $lead['id'] = $lead['id'] ?? uniqid('lead_', true);
    $lead['created_at'] = $lead['created_at'] ?? date('Y-m-d H:i:s');
    $lead['source'] = $lead['source'] ?? 'contact_form';

    $leads[] = $lead;

    file_put_contents(get_leads_storage_path(), json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
