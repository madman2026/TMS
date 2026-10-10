# راهنمای CLI پذیرش TMS

این راهنما قرارداد نسخهٔ ۲ کلاینت Artisan را برای ۱۸ عملیات Acceptance توضیح می‌دهد. فرمان‌ها فقط ورودی را دریافت، آن را به operation متناظر ارسال و نتیجهٔ امن را نمایش می‌دهند. تصمیم‌های دامنه، پایدارسازی، queue، target و اجرا در لایهٔ operation/service باقی می‌ماند.

## حالت‌های اجرا

حالت پیش‌فرض و `--json` برای script هستند: دقیقاً یک JSON فشرده در stdout خروجی داده می‌شود و هیچ prompt، animation یا متن انسانی به آن اضافه نمی‌شود.

`--interactive` حالت انسانی CLI را فعال می‌کند و در صورت نیاز مقادیر را با Laravel Prompts می‌پرسد. تمام labelها، validation messageها، confirmationها، tableها و راهنمای خطای خود CLI فقط انگلیسی هستند؛ فارسی فقط زبان این راهنمای اپراتور است. این گزینه با `--json`، `--no-interaction` یا terminal غیرتعاملی سازگار نیست و با `cli_mode_invalid` و exit 2 رد می‌شود. Ctrl+C در prompt با `cli_interrupted` و exit 130 متوقف می‌شود و عملیات تغییردهنده را فراخوانی نمی‌کند.

در این حالت هر ۱۸ فرمان عنوان آغاز، promptهای اعتبارسنجی‌شده، spinner اجرای operation و summary/table پایان را از Laravel Prompts می‌گیرند. فیلترهای catalog و coverage، تنظیمات runtime، pagination، mode اجرا و فایل‌های prerequisite فقط در صورت نیاز پرسیده می‌شوند. در Windows و تست‌ها fallback رسمی Laravel استفاده می‌شود و raw terminal behavior تحمیل نمی‌شود.

## هویت و selectorها

هویت اجرایی کامل است:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

کلیدهای hierarchy حداکثر ۶۴ نویسه دارند و از حرف کوچک انگلیسی، رقم، نقطه، underscore و خط تیره استفاده می‌کنند. این کلیدها باید غیرمحرمانه باشند.

selectorهای تکرارپذیر `--app`، `--component`، `--suite`، `--scenario`، `--variant`، `--capability`، `--tag`، `--disposition` و `--evidence-mode` هستند. بین مقادیر یک گزینه OR و بین گزینه‌های مختلف AND اعمال می‌شود. wildcard، regex، نفی و مقدار commaدار پشتیبانی نمی‌شود. پیش‌فرض `--limit` برای selectorها ۱۰۰۰ است.

## فرمان‌ها

| فرمان | ورودی script |
|---|---|
| `acceptance:list` | selectorهای اختیاری |
| `acceptance:plan` | selectorهای اختیاری؛ فقط موارد executable |
| `acceptance:run` | `app component suite scenario variant profile` و گزینه‌های runtime |
| `acceptance:app:create` | `module app`؛ پیش‌فرض dry-run |
| `acceptance:app:validate` | `module` |
| `acceptance:component:create` | `module component`؛ پیش‌فرض dry-run |
| `acceptance:scenarios:import` | `module --mappings=<json-file>`؛ پیش‌فرض dry-run |
| `acceptance:prerequisite:prepare` | `--request-id=<uuid>` یا tuple کامل و profile |
| `acceptance:prerequisite:input:submit` | `request lock-version --inputs=<json-file>` |
| `acceptance:prerequisite:approval:grant` | `request lock-version scope --confirm` |
| `acceptance:prerequisite:request:cancel` | `request lock-version --confirm` |
| `acceptance:batch:start` | `profile mode`، selectorها و `--prerequisites=<json-file>` اختیاری |
| `acceptance:batch:resume` | `batch operation lock-version` و prerequisite اختیاری |
| `acceptance:batch:item:retry` | `item lock-version --confirm` |
| `acceptance:batch:cancel` | `batch operation lock-version --confirm` |
| `acceptance:status` | دقیقاً یکی از `--batch` یا `--operation` |
| `acceptance:report` | `batch`، `--after-item` و `--limit` اختیاری |
| `acceptance:coverage` | `app`، `--after-source-case`، `--disposition` و `--limit` اختیاری |

مثال‌ها:

```powershell
php artisan acceptance:list --app=example-app --component=example-component --json
php artisan acceptance:run example-app example-component example-suite example-scenario example-variant 42 --json
php artisan acceptance:run --interactive
php artisan acceptance:app:create ExampleModule example-app --json
php artisan acceptance:app:create ExampleModule example-app --apply --confirm --json
php artisan acceptance:batch:start 42 async --app=example-app --json
php artisan acceptance:status --batch=17 --json
php artisan acceptance:report 17 --limit=100 --json
php artisan acceptance:coverage example-app --disposition=automated-full --json
```

`acceptance:run` همچنين `--request-id`، `--browser`، `--headed`، `--timeout` و `--slow-mo` را می‌پذیرد. batch با mode برابر `async` برای ادامهٔ اجرا به queue worker از پیش پیکربندی‌شده نیاز دارد؛ CLI worker را راه‌اندازی یا تنظیم نمی‌کند.

## dry-run و تأیید

create/import بدون `--apply` فقط برنامهٔ تغییر را برمی‌گرداند. در script، اعمال تغییر به هر دوی `--apply --confirm` نیاز دارد. approval، prerequisite cancellation، item retry و batch cancellation نیز `--confirm` می‌خواهند. در interactive، تأیید با prompt گرفته می‌شود. این تأیید UI جایگزین approval پایدار دامنه نیست.

گزینهٔ force یا overwrite وجود ندارد. برخورد مسیر یا فایل مدیریت‌نشده توسط service رد می‌شود.

## فایل‌های JSON

- `--mappings`: لیست `SourceCaseMapping`، حداکثر ۱ MiB.
- `--prerequisites`: لیست ارجاع‌های tuple/request برای batch، حداکثر ۱ MiB.
- `--inputs`: map کلیدهای prerequisite، حداکثر ۶۴ KiB. هر مقدار دقیقاً `source` و `value` دارد.

فایل باید UTF-8، قابل خواندن و یک JSON معتبر باشد. مسیر و محتوای فایل در output یا log تکرار نمی‌شود. مقدار محرمانه نباید در argv قرار گیرد؛ برای آن `source: secret_reference` و یک مرجع محرمانه ارسال کنید.

در interactive input submission، CLI ابتدا درخواست را به‌صورت read-only کشف می‌کند، سپس مقادیر را بر اساس type می‌پرسد و فقط پس از آن submit را فراخوانی می‌کند. مرجع‌های محرمانه با prompt مخفی دریافت می‌شوند.

## قرارداد JSON و exit

خروجی موفق/ناموفق `list`، `plan` و `run` همان projection پذیرفته‌شدهٔ نسخهٔ ۲ را حفظ می‌کند. سایر فرمان‌ها envelope زیر را با همین ترتیب دارند:

```text
schema_version, operation, status, error_code, correlation_id, operation_id,
retryable, permanent, admin_action_required, errors, data
```

`errors` همیشه object است و در حالت خالی `{}` می‌ماند. `data` در نبود نتیجه `null` و در موفقیت نگاشت صریح DTO امن است. enumها با مقدار رشته‌ای و timestampها با ISO-8601 نمایش داده می‌شوند. شناسهٔ خام target resource نمایش داده نمی‌شود و فقط `type` و `reference_hash` مجاز است.

| نتیجه | exit |
|---|---:|
| succeeded | 0 |
| rejected یا خطای ورودی محلی | 2 |
| failed | 1 |
| قطع prompt توسط کاربر | 130 |

خطاهای محلی CLI عبارت‌اند از `cli_mode_invalid`، `cli_input_invalid`، `cli_confirmation_required` و `cli_interrupted`. خطاهای دامنه از `error_code` نتیجهٔ operation می‌آیند؛ ورودی ردشده، exception خام، credential، token، selector، payload یا محتوای فایل هرگز در خروجی یا log بازتاب داده نمی‌شود. در حالت interactive، خلاصه و جدول موفق در stdout و خطای انسانی کوتاه در stderr نوشته می‌شود. با `-v` شناسه‌های امن correlation و operation نیز نمایش داده می‌شوند.
