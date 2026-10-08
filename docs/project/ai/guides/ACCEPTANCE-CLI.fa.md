# راهنمای CLI پذیرش TMS

این راهنما قرارداد نسخهٔ ۲ CLI پذیرش را توضیح می‌دهد. Appها در سورس تعریف می‌شوند و با قرارداد کامل `AcceptanceComponentProvider` در `AcceptanceAppRegistry` ثبت می‌شوند. هیچ کشف خودکار فایل، تعریف گردش کار در پایگاه داده یا App واقعی توسط این راهنما ایجاد نمی‌شود.

## هویت اجرایی

هر مورد پذیرش یک هویت کامل و صریح دارد:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

کلیدهای App، Component، Suite، Scenario و Variant با حرف کوچک انگلیسی یا رقم شروع می‌شوند و ادامهٔ آن‌ها می‌تواند حرف کوچک، رقم، نقطه، underscore یا خط تیره باشد. طول هر کلید حداکثر ۶۴ نویسهٔ ASCII است. این کلیدها باید غیرمحرمانه باشند؛ رمز، cookie، token، URL، selector یا payload را در آن‌ها قرار ندهید.

## مشاهده و برنامه‌ریزی

```powershell
php artisan acceptance:list --app=example-app --component=example-component --suite=example-suite
php artisan acceptance:plan --app=example-app --component=example-component --suite=example-suite --disposition=automated
```

`acceptance:list` همهٔ Variantهای منطبق را نمایش می‌دهد. `acceptance:plan` فقط ردیف‌های `automated` را در `items` می‌آورد. dispositionهای فعال عبارت‌اند از:

- `automated`
- `blocked`
- `not-implemented`

هر دو فرمان فقط JSON چاپ می‌کنند و Profile، Test، Step یا مرورگر ایجاد نمی‌کنند. inspection کاتالوگ نیز runnable scenario را resolve نمی‌کند.

گزینه‌های تکرارپذیر انتخاب عبارت‌اند از:

| گزینه | موضوع |
|---|---|
| `--app` | App ثبت‌شده |
| `--component` | Component |
| `--suite` | Suite متعلق به Component |
| `--scenario` | Scenario متعلق به Suite |
| `--variant` | Variant سناریو |
| `--capability` | قابلیت طبقه‌بندی |
| `--tag` | برچسب طبقه‌بندی |
| `--disposition` | وضعیت خودکارسازی |
| `--evidence-mode` | شیوهٔ evidence |

مقدارها کلید دقیق‌اند. wildcard، regex، نفی و مقدار commaدار پشتیبانی نمی‌شود. بین مقدارهای یک گزینه OR و بین گزینه‌های متفاوت AND اعمال می‌شود. Component و Suite بخش هویت سلسله‌مراتبی هستند و classification محسوب نمی‌شوند.

## خروجی نسخهٔ ۲

خروجی موفق list/plan این فیلدها را دارد:

- `schema_version`: عدد ۲؛
- `status`: یکی از `listed` یا `planned`؛
- `plan_version`: عدد ۲؛
- `catalog_versions`: نسخهٔ کاتالوگ Appهای انتخاب‌شده؛
- `counts`: تعداد matched، executable، excluded و هر disposition؛
- `fingerprint`: SHA256 دادهٔ canonical؛
- `items`: ردیف‌های امن metadata.

هر ردیف شامل `app_key`، `component_key`، `suite_key`، `scenario_key`، `variant_key`، `capabilities`، `tags`، `disposition`، `evidence_mode` و `executable` است. ترتیب ردیف‌ها با tuple کامل تعیین می‌شود و tuple کامل در fingerprint شرکت می‌کند.

نسخهٔ کاتالوگ باید با هر تغییر تعریف hierarchy یا metadata تغییر کند. planner نسخه را پیش و پس از پیمایش بررسی می‌کند و تغییر هم‌زمان را با `acceptance_catalog_changed` رد می‌کند.

## اجرای یک Variant

فرمان run تمام اجزای هویت را به‌صورت positional و اجباری دریافت می‌کند:

```powershell
php artisan acceptance:run example-app example-component example-suite example-scenario example-variant 42
```

ترتیب آرگومان‌ها چنین است:

```text
acceptance:run app component suite scenario variant profile
```

گزینه‌های `--browser`، `--headed`، `--timeout` و `--slow-mo` در دسترس‌اند. dispatcher ابتدا همان tuple را در plan معتبر می‌کند، سپس فقط همان Scenario را resolve می‌کند. Variant باید صریح باشد و فقط disposition `automated` قابل اجرا است.

خروجی موفق یا ناموفق دارای `schema_version: 2` است. نتیجه‌ای که Test دارد، tuple کامل، `test_id`، `status` و `error_code` را برمی‌گرداند. Test جدید نیز پنج کلید هویت را به‌صورت non-null ذخیره می‌کند.

## قرارداد provider

هر App ثبت‌شده `App\Contracts\AcceptanceComponentProvider` را کامل پیاده‌سازی می‌کند:

- `key()` و `catalogVersion()`؛
- `components()`؛
- `suites()`؛
- `scenarios()`؛
- `variants(scenarioKey)`؛
- `resolveScenario(componentKey, suiteKey, scenarioKey, variantKey)`.

Component، Suite و Scenario در محدودهٔ App کلید یکتا دارند. Suite باید به Component موجود اشاره کند و Scenario باید Component/Suite منطبق داشته باشد. متدهای metadata باید تکرارپذیر، محدود و بدون اثر اجرایی باشند. runnable فقط با tuple دقیق resolve می‌شود.

## سقف‌ها

`--limit` عدد ASCII مثبت از ۱ تا ۱۰۰۰ است و سقف ردیف‌های منطبق را تعیین می‌کند. این گزینه pagination نیست؛ مشاهدهٔ ردیف اضافه عملیات را با `acceptance_catalog_limit_exceeded` متوقف می‌کند. سقف پیمایش metadata در هر عملیات ۱۰٬۰۰۰ مورد است.

## خطا و رفع مشکل

خروجی خطا یک خط JSON شامل `schema_version`، `status` و `error_code` است. خروج موفق کد ۰، rejected کد ۲ و failed کد ۱ دارد.

| error_code | اقدام |
|---|---|
| `operation_request_invalid` | نسخه، فیلدهای مجاز و tuple کامل را بررسی کنید. |
| `acceptance_selector_invalid` | شکل کلید، enum و limit را اصلاح کنید. |
| `acceptance_app_not_found` | ثبت App و کلید انتخاب‌شده را بررسی کنید. |
| `acceptance_selector_not_found` | وجود Component/Suite/Scenario/Variant را در محدوده بررسی کنید. |
| `acceptance_hierarchy_invalid` | ارجاع Component/Suite/Scenario را اصلاح کنید. |
| `acceptance_hierarchy_duplicate` | هویت تکراری در hierarchy را حذف کنید. |
| `acceptance_catalog_invalid` | نوع descriptor، metadata یا runnable برگشتی را اصلاح کنید. |
| `acceptance_catalog_limit_exceeded` | انتخاب را محدود کنید. |
| `acceptance_catalog_changed` | نسخه و تعریف ناپایدار provider را اصلاح و دوباره plan کنید. |
| `acceptance_catalog_failed` | provider را در سورس بررسی کنید. |
| `acceptance_variant_not_executable` | یک Variant با disposition برابر `automated` انتخاب کنید. |

مرز مشترک عملیات برای run موفق event ثابت `tms.acceptance.operation.completed` و برای خطا `tms.acceptance.operation.failed` ثبت می‌کند. context امن run شامل tuple کامل، شناسه‌های trace، classification خطا و در صورت وجود `test_id` است. exception خام، credential، target payload یا ورودی ردشده در JSON و log قرار نمی‌گیرد.
