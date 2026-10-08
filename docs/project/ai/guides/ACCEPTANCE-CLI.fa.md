# راهنمای CLI پذیرش TMS

این راهنما متعلق به پروژهٔ TMS است و از پروفایل پروژه و فهرست راهنماها قابل دسترسی است.

## پیش‌نیاز و محدوده

App و سناریوها در سورس تعریف و به‌صورت صریح در `AcceptanceAppRegistry` ثبت می‌شوند. پیمایش فایل‌ها، کشف خودکار ماژول‌ها یا تعریف گردش کار قابل‌اجرا در پایگاه داده وجود ندارد. این Pack هیچ App واقعی ثبت نمی‌کند؛ نمونه‌های زیر کلیدهای فرضی و دادهٔ مصنوعی دارند.

کار با هدف واقعی به Pack اختصاصی آن App و تأیید محیط testing/staging نیاز دارد. دادهٔ تولید، دادهٔ واقعی مشتری و حساب واقعی در این محدوده نیست. کلید، metadata و نسخه باید شناسه‌های غیرمحرمانهٔ بررسی‌شده باشند؛ رمز، cookie، token، وضعیت ورود، URL، selector یا payload را در آن‌ها قرار ندهید. معتبر بودن شکل یک رشته، غیرمحرمانه بودن معنای آن را ثابت نمی‌کند.

## مشاهده و برنامه‌ریزی

پس از ثبت صریح یک App مصنوعیِ تأییدشده، می‌توانید کاتالوگ آن را مشاهده کنید:

```powershell
php artisan acceptance:list --app=example-app
php artisan acceptance:plan --app=example-app --suite=example-suite
```

این‌ها نمونهٔ استفاده‌اند؛ اجرای خارجی یا ثبت App با این راهنما مجاز نمی‌شود. برای کاتالوگ خالیِ ثبت‌شده، خروجی موفق با تعداد صفر دریافت می‌کنید. کلید App ناشناخته خطا است.

`acceptance:list` تمام variantهای منطبق را، حتی برای dispositionهای غیرخودکار، نمایش می‌دهد. `acceptance:plan` فقط ردیف‌های `automated` را در `items` قرار می‌دهد. `manual-only`، `blocked` و `not-implemented` در شمارش‌ها باقی می‌مانند و وارد طرح قابل‌اجرا نمی‌شوند.

هر دو فرمان فقط JSON چاپ می‌کنند. Profile نمی‌خواهند و Test/Step، مرورگر، fixture یا حساب آزمایشی ایجاد یا دریافت نمی‌کنند. ساخت یک طرح، هیچ سناریویی را اجرا نمی‌کند و چیزی روی دیسک یا در DB ذخیره نمی‌شود.

## انتخاب‌گرها

هر یک از گزینه‌های زیر را می‌توان چند بار نوشت:

| گزینه | موضوع |
|---|---|
| `--app` | Appهای ثبت‌شده |
| `--scenario` | کلید سناریو |
| `--variant` | کلید variant |
| `--suite` | مجموعهٔ سناریو |
| `--capability` | قابلیت |
| `--tag` | برچسب |
| `--disposition` | مقدار یکی از چهار disposition |
| `--evidence-mode` | مقدار یکی از سه evidence mode |

مقدارها کلید دقیق‌اند. wildcard، regex، عبارت commaدار، نفی و عبارت آزاد پشتیبانی نمی‌شود. بین مقدارهای یک گزینه OR و بین گزینه‌های متفاوت AND اعمال می‌شود؛ تکرار یک مقدار حذف و ترتیب ورودی برای fingerprint یکسان‌سازی می‌شود.

```powershell
php artisan acceptance:plan --app=example-app --tag=tag-a --tag=tag-b --suite=example-suite --disposition=automated
```

این نمونه ردیف‌هایی از App انتخاب‌شده را می‌گیرد که در suite مشخص هستند و حداقل یکی از دو tag را دارند و automated هستند.

کلیدها با حرف کوچک انگلیسی یا رقم شروع می‌شوند؛ ادامهٔ آن‌ها می‌تواند حرف کوچک، رقم، نقطه، underscore یا خط تیره باشد. حداکثر طول ۶۴ نویسهٔ ASCII است. suites/capabilities/tags هر سناریو حداکثر ۶۴ کلید یکتا دارند.

App ابتدا بررسی می‌شود. شناخته بودن scenario/suite/capability/tag در تمام descriptorهای Appهای انتخاب‌شده بررسی می‌شود، حتی اگر فیلتر دیگری آن ردیف را حذف کند. شناخته بودن variant در سناریوهای انتخاب‌شده، پیش از تقاطع فیلترهای classification بررسی می‌شود. گزینهٔ ناشناخته خطا است؛ فیلترهای شناخته‌شده با تقاطع خالی، نتیجهٔ موفق و خالی می‌دهند.

بدون `--app` تمام Appهای ثبت‌شده در محدوده‌اند. Appهای انتخاب‌نشده پیمایش نمی‌شوند. بدون `--variant`، سناریویی که classification آن منطبق نیست، نیاز به گسترش variant ندارد. یکتایی فقط برای هویت‌های واقعاً پیمایش‌شده بررسی می‌شود.

## خروجی و fingerprint

فیلدهای خروجی موفق:

- `status`: یکی از `listed` یا `planned`؛
- `plan_version`: عدد ۱؛
- `catalog_versions`: نسخهٔ metadata کاتالوگ هر App انتخاب‌شده؛
- `counts`: تعداد matched، executable، excluded و تعداد هر disposition؛
- `fingerprint`: SHA256 انتخاب canonical؛
- `items`: ردیف‌های metadata.

هر ردیف فقط app_key، scenario_key، variant_key، suites، capabilities، tags، disposition، evidence_mode و executable دارد. نام نمایشی سناریو، دادهٔ Profile، ورودی اجرا، credentials، exception، step و وضعیت ورود در خروجی نیست.

شمارش‌ها مربوط به تقاطع منتخب‌اند و ادعای اندازهٔ کل کاتالوگ ندارند. ترتیب ردیف‌ها با tuple `(App, scenario, variant)` تعیین می‌شود؛ ترتیب labelها نیز canonical است. کلیدهای یکسان در دو App متفاوت، دو هویت مجزا هستند.

list و plan با انتخاب‌گرها و سقف یکسان fingerprint یکسان دارند، با وجود تفاوت projection ردیف‌ها. نسخهٔ طرح، انتخاب‌گرهای معتبر و canonical، سقف، نسخهٔ Appها و تمام ردیف‌های منطبق در hash شرکت می‌کنند. ترتیب provider/گزینه‌ها یا دادهٔ خصوصی provider در hash شرکت نمی‌کند. تغییر identity، classification، revision یا انتخاب‌گر/سقف می‌تواند fingerprint را تغییر دهد.

provider باید با تغییر تعریف کاتالوگ revision غیرمحرمانهٔ خود را تغییر دهد. revision پیش و پس از planning و پس از پیمایش Appهای دیگر بررسی می‌شود. `legacy-v1` فقط نسخهٔ adapter قدیمی است؛ تضمین کامل revision سورس نیست. طرح قدیمی، مجوز اجرای آینده یا تضمین ثابت ماندن هدف واقعی نیست.

evidence mode یکی از `metadata-only`، `non-sensitive-visual` و `sensitive-no-capture` است. این مقدار classification توصیفی است؛ وجود آن، اعمال سیاست capture در runtime را ثابت نمی‌کند.

## سقف‌ها

حداکثر ۱۰۰۰ variant منطبق در یک عملیات نگهداری می‌شود. `--limit` عدد ASCII مثبت از ۱ تا ۱۰۰۰ است و فقط سقف ردیف‌های منطبق را پایین می‌آورد:

```powershell
php artisan acceptance:plan --app=example-app --scenario=example-scenario --limit=10
```

این گزینه pagination یا قطع خروجی نیست. رسیدن به ردیف اضافی خطا می‌دهد و طرح یا fingerprint ناقص چاپ نمی‌شود. ردیف‌های غیرخودکار هم در سقف matched حساب می‌شوند.

سقف مجموع مشاهدهٔ descriptor/variant در هر عملیات ۱۰٬۰۰۰ است. اولین عنصر اضافه ممکن است برای تشخیص overflow دریافت شود، سپس generator دیگر جلو نمی‌رود. شکست سقف بر تکمیل بررسی vocabulary مقدم است؛ انتخاب را محدود کنید. مقدار scan budget قابل تنظیم نیست.

در adapter قدیمی، cache یک App حداکثر ۱۰٬۰۰۰ سناریوی قابل‌اجرا دارد. برای App بزرگی که با interface قدیمی ارائه می‌شود، محدود کردن metadata لزوماً ساخت runnableهای همان App را کاهش نمی‌دهد؛ برای discovery بدون ساخت runnable باید provider جدید را پیاده‌سازی کنید.

## قرارداد App و مسیر سازگاری

ثبت App فقط هویت و تکراری نبودن کلید App را بررسی می‌کند و هیچ سناریو/descriptor/variant را پیمایش نمی‌کند. خطای تعریف سناریوهای قدیمی اکنون هنگام اولین lookup یا inspection رخ می‌دهد؛ cache کامل فقط پس از اعتبارسنجی موفق منتشر می‌شود. شکست discovery، App دیگری را خراب نمی‌کند و cache ناقص باقی نمی‌گذارد.

interfaceهای Core تغییر نکرده‌اند. یک App می‌تواند علاوه بر قرارداد موجود، `App\Contracts\AcceptanceCatalogProvider` را پیاده‌سازی کند:

- `catalogVersion()`: revision غیرمحرمانهٔ code-owned؛
- `descriptors()`: iterable تنبلِ ScenarioDescriptor شامل کلید و ScenarioMetadata؛
- `variants(scenarioKey)`: iterable تنبلِ VariantDescriptor شامل کلید؛
- `resolveScenario(scenarioKey, variantKey)`: ساخت فقط سناریوی درخواست‌شده.

سه متد discovery باید تکرارپذیر و بدون اثر اجرایی باشند و حساب یا credential دریافت نکنند. variant، classification سناریوی خود را به ارث می‌برد و payload یا override ندارد. دادهٔ خصوصی و رفتار اجرایی در سورس App می‌ماند. در list/plan این provider، متد قدیمی `scenarios()` و factory اجرا فراخوانی نمی‌شوند.

App قدیمی برای هر سناریو variant با کلید `default` دارد. adapter ممکن است runnableهای همان App را هنگام lookup بسازد؛ interface قدیمی تضمین descriptor-only نمی‌دهد. نام‌ها، steps و اطلاعات خصوصی آن runnableها وارد خروجی یا hash نمی‌شوند.

امضای `acceptance:run app scenario profile` و گزینه‌های قبلی مرورگر تغییر نکرده‌اند. App قدیمی مسیر تک‌سناریوی قبلی را دارد. App دارای provider فقط variant `default` را از این فرمان resolve می‌کند؛ نبود default یا غیرخودکار بودن ردیف، پیش از lookup Profile رد می‌شود. گزینهٔ اجرای variant جدید اضافه نشده است.

قید automated در طرح و dispatcher اعمال می‌شود. اجرای مستقیم App بدون provider، رفتار قبلی را نگه می‌دارد و این Pack قید disposition تازه‌ای به آن مسیر اضافه نمی‌کند.

dispatcher یک tuple خودکار را مجدداً به‌شکل محدود انتخاب می‌کند، فقط factory همان tuple را فراخوانی می‌کند و کلید و metadata برگشتی را تطبیق می‌دهد. این مرحله فقط ساخت object است؛ steps، Runner و persistence را اجرا نمی‌کند. اجرای batch و ذخیره/ادامهٔ طرح در این Pack وجود ندارد.

## خطا و رفع مشکل

خروجی خطا یک خط JSON با `status` و `error_code` است. متن exception و ورودی ردشده چاپ نمی‌شود. خروج موفق کد ۰، rejected کد ۲ و failed کد ۱ دارد.

| error_code | اقدام |
|---|---|
| `acceptance_selector_invalid` | شکل کلید، enum و limit را اصلاح کنید. |
| `acceptance_app_not_found` | ثبت صریح App و کلید انتخاب‌شده را بررسی کنید. |
| `acceptance_selector_not_found` | vocabulary کاتالوگ در محدودهٔ انتخاب‌شده را بررسی کنید. |
| `acceptance_catalog_invalid` | نوع، کلید، metadata یا نتیجهٔ factory در سورس provider را اصلاح کنید. |
| `acceptance_catalog_duplicate` | کلید تکراری در App/سناریوی پیمایش‌شده را اصلاح کنید. |
| `acceptance_catalog_limit_exceeded` | انتخاب را محدود کنید؛ limit را ابزار pagination فرض نکنید. |
| `acceptance_catalog_changed` | تعریف/revision ناپایدار را بررسی و پس از اصلاح صریحاً دوباره برنامه‌ریزی کنید. |
| `acceptance_catalog_failed` | provider را در سورس بررسی کنید؛ متن خام خطا در log درج نمی‌شود. |
| `acceptance_variant_not_executable` | tuple دارای disposition خودکار را انتخاب کنید. |

خطاهای list/plan/run در مرز مشترک عملیات، یک بار با event ثابت `tms.acceptance.operation.failed` ثبت می‌شوند: rejected با warning و failed با error. context امن شامل `operation`، `error_code`، `correlation_id`، `operation_id`، `retryable`، `permanent` و `admin_action_required` است؛ `test_id` فقط وقتی RunService نتیجهٔ Test را برگرداند اضافه می‌شود. اجرای موفق run با `tms.acceptance.operation.completed` در سطح info ثبت می‌شود؛ موفقیت list/plan log جدید ندارد. فرمان‌ها log خطای دوم تولید نمی‌کنند و logهای داخلی موجود RunService/Core ادامه دارند. اطلاعات target/provider، حساب، stack trace یا ورودی خام را به log اضافه نکنید.

خروجی JSON، گزینه‌ها و exit codeهای CLI حفظ شده‌اند؛ کد خام ناشناختهٔ خطا با `acceptance_command_failed` جایگزین می‌شود. شناسه‌های correlation/operation در نتیجهٔ سرویس و log مشترک قرار دارند و به JSON یا گزینه‌های فعلی CLI اضافه نشده‌اند. `operation_id` فقط برای run ساخته می‌شود و این شناسه‌ها در Test/Step ذخیره نمی‌شوند. اگر RunService پس از ایجاد Test خطا بدهد ولی شناسهٔ آن را برنگرداند، log مرزی `test_id` ندارد. این اتصال محدود به نتیجه و log است؛ ردیابی کامل target، retry خودکار یا audit/persistence جدید ایجاد نشده است.
