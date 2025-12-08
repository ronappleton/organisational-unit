
# 🧩 Semantic Wrapper Models – Detailed Guide

## Why use semantic wrappers?
To improve DX by creating meaningful domain-specific models.

---

# ✔️ Examples of wrappers

- `Category` wrapper for e-commerce.
- `BinLocation` wrapper for WMS.
- `SchoolUnit` wrapper for MIS.
- `NavItem` wrapper for SaaS.

---

# 🛠 Example Wrapper

```php
class WarehouseLocation {
    public static function bins() {
        return OU::query()->ofType('bin');
    }

    public static function createBin($name, $parentId) {
        return OU::create([
            'name' => $name,
            'type' => 'bin',
            'parent_id' => $parentId,
        ]);
    }
}
```

---

# ✔️ When wrappers help
- You want developers to see *domain language*, not OU language.
- You want reusable helper methods.
- You want semantic meaning for `type` values.

---

# ❌ When wrapper is NOT needed
- Internal system utilities.
- OU manipulation scripts.
