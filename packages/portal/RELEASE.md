# Portal Release Guide

## Publishing to Packagist

### Initial Setup

1. **Create GitHub repo**
```bash
cd packages/portal
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/thaumware/portal.git
git push -u origin main
```

2. **Register on Packagist**
- Go to https://packagist.org
- Submit: https://github.com/thaumware/portal
- Enable auto-update webhook

### Versioning (Semantic)

```
MAJOR.MINOR.PATCH

1.0.0 - Initial release
1.0.1 - Bug fix
1.1.0 - New feature (backwards compatible)
2.0.0 - Breaking change
```

### Release Process

```bash
# 1. Update CHANGELOG.md
echo "## [1.0.0] - 2025-11-09
- Initial release
- Core: PortalRegistry, RelationLoader
- Adapters: IlluminateAdapter (Laravel)
" >> CHANGELOG.md

# 2. Commit changes
git add .
git commit -m "Release 1.0.0"

# 3. Create tag
git tag -a v1.0.0 -m "Release version 1.0.0"

# 4. Push
git push origin main
git push origin v1.0.0
```

Packagist updates automatically via webhook.

### Quick Release

```bash
# One-liner
git tag v1.0.1 -m "Fix: batch loading" && git push origin v1.0.1
```

### Install in Projects

```bash
composer require thaumware/portal
```

For dev versions:
```json
"require": {
    "thaumware/portal": "dev-main"
}
```

### Testing Before Release

```bash
php tests/PortalTest.php
```

Expected output:
```
Running Portal Tests...

✓ Register origin: origin-1
✓ Link models
✓ Attach relations: [...]
✓ Deactivate origin

All tests passed!
```
