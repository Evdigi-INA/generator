# Asset Extraction Plan (Strategy B — Future Major Version)

**Status:** Planned, not implemented
**Target version:** 3.0 (next major)
**Tracking issue:** TBD

## Goal

Remove 6,043 third-party asset files from repo. Package ships PHP + own Blade stubs only. 36x smaller clone. True Laravel package identity.

## Scope

**Remove from repo:**
- `assets/extensions/` — 5,876 files (30+ third-party JS/CSS libs)
- `assets/compiled/` — generated build output
- `assets/static/` — vendored static assets

**Keep in repo:**
- `stubs/` — own Blade stubs (generator templates)
- `src/` — PHP source
- `views/` — generator form views
- `assets/` directory deleted entirely

## Asset inventory & target strategy

| Library | Files | Target | Reason |
|---------|-------|--------|--------|
| bootstrap-icons | ~2,000 | composer `twbs/bootstrap-icons` | stable, small icons |
| feather-icons | ~280 | composer | small icon set |
| datatables.net-bs5 | ~50 | composer `npm-asset/datatables.net-bs5` | core lib |
| chart.js | ~20 | CDN in stub | user picks version |
| chartjs-* plugins | ~30 | CDN | optional |
| jquery | ~50 | CDN | legacy only |
| summernote | ~200 | CDN | editor alternative |
| tinymce | ~400 | CDN | heavy, user already has |
| quill | ~100 | CDN | editor alt |
| flatpickr | ~20 | CDN | date picker |
| sweetalert2 | ~10 | CDN | modal |
| toastify-js | ~5 | CDN | notifications |
| apexcharts | ~30 | CDN | charts |
| jsvectormap | ~20 | CDN | maps |
| dragula | ~5 | CDN | drag drop |
| parsleyjs | ~10 | CDN | form validation |
| perfect-scrollbar | ~10 | CDN | scrollbar |
| filepond + 6 plugins | ~50 | CDN | file upload |
| rater-js | ~5 | CDN | rating |
| simple-datatables | ~10 | CDN | datatable alt |
| choices.js | ~10 | CDN | select enhancer |
| dayjs | ~5 | CDN | date util |
| @fortawesome | ~500 | CDN | icons alt |
| @icon/dripicons | ~250 | CDN | icons |

**Total vendored:** ~4,070 files in `extensions/` + 1,800+ in `static/` + `compiled/`

## Implementation steps

### Phase 1: Inventory + categorize (already done above)

### Phase 2: Stub rewrite
Replace all `{{ asset('extensions/...') }}` references in stubs with:

```blade
@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
```

CDN: `https://cdn.jsdelivr.net/npm/{package}@{version}/{file}`

### Phase 3: Publish command
Update `PublishAllFiles.php` to:
- Remove asset publish step (assets no longer in repo)
- Keep: views, config, providers, models, bootstrap

### Phase 4: Composer script (optional)
Add `post-autoload-dump` script for install hints:

```json
"scripts": {
    "post-autoload-dump": [
        "@php artisan vendor:publish --tag=generator-config --no-interaction || true",
        "@php artisan vendor:publish --tag=generator-views --no-interaction || true"
    ]
}
```

### Phase 5: Migration guide
CHANGELOG entry:

```markdown
## 3.0.0 - Asset extraction

**BREAKING:** Third-party assets no longer shipped in package.

Before upgrade:
1. Backup your custom asset overrides from `public/vendor/generator/`
2. Upgrade to 3.0: `composer update evdigiina/generator`
3. Republish config: `php artisan vendor:publish --tag=generator-config --force`
4. Clear views: `php artisan view:clear`
5. Re-link CDN or local asset paths per your preferences
```

## Trade-offs accepted

- **Offline install** requires pre-cached CDN or local asset mirror
- **First page load** slightly slower (CDN miss = cache miss)
- **User customization** of asset paths = manual edit in published views

## Rollback strategy

If adoption fails:
- Tag `2.x` as last asset-shipping release
- Keep `3.x` branch as opt-in CDN-only
- LTS support for 2.x: 6 months

## Decision

Wait until 2.x adoption stabilizes. Target: 3 months after 2.0 GA.
