
# 🏢 SaaS Multi-Tenant – Full Example

```mermaid
graph TD
    Tenant1["Tenant 1"] --> Nav1["Navigation Tree"]
    Tenant1 --> Features1["Feature Groups"]

    Tenant2["Tenant 2"] --> Nav2["Navigation Tree"]
    Tenant2 --> Categories2["Categories"]
```

## ✔️ OU used for
- Per-tenant structure.
- Navigation trees.
- Feature modules and groupings.
- Custom per-tenant metadata.

## ❌ Not used for
- Billing.
- Seats/subscriptions.
- Users & permissions.

---

# 🌐 Create a tenant structure

```php
$root = OU::create([
    'tenant_id' => $tenant->id,
    'name' => 'Root',
    'type' => 'tenant_root',
]);
```

Add navigation items:

```php
OU::create(['tenant_id' => $tenantId, 'name' => 'Products', 'type' => 'nav', 'parent_id' => $root->id]);
OU::create(['tenant_id' => $tenantId, 'name' => 'Analytics', 'type' => 'nav', 'parent_id' => $root->id]);
```

---

# 🧠 Metadata Example

```php
$root->setMeta('theme', 'dark');
$root->setMeta('plan', 'pro');
```
