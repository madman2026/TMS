# STRUCTURE-GUIDE — راهنمای ساختار مستندات AI

این فایل راهنمای انسانی ساختار جدید `docs/ai` است. هدف آن این است که اپراتور، برنامه‌نویس و AI بدانند هر پوشه برای چه کاری است، چه فایل‌هایی داخل آن قرار می‌گیرند، چه زمانی باید خوانده شوند و چه زمانی باید آپدیت شوند.

این فایل **فایل شروع اجباری هر چت Codex نیست**. برای شروع هر چت، مسیر اصلی همچنان این است:

1. `AGENTS.md`
2. `docs/ai/start/START-HERE.md`
3. `docs/ai/start/CONTEXT-MAP.md`

این فایل فقط وقتی خوانده می‌شود که اپراتور یا AI بخواهد ساختار کلی مستندات را بفهمد، فایل جدیدی در جای درست ایجاد کند، یا مسیر تولید و نگهداری فایل‌ها را بررسی کند.

---

## 1. اصل طراحی ساختار جدید

ساختار جدید بر اساس این اصل ساخته شده است:

```text
همه چیز مستند باشد، اما همه چیز هر بار خوانده نشود.
```

در ساختار قدیمی، AI ممکن بود در ابتدای هر چت تعداد زیادی فایل بخواند و context زیادی مصرف کند. در ساختار جدید، AI ابتدا فقط فایل‌های مسیریاب را می‌خواند و بعد بر اساس Release، Phase، Pack، Conflict، Decision یا Review فقط فایل‌های لازم را باز می‌کند.

اصول اصلی:

- `AGENTS.md` سبک است و نقش Router دارد.
- `START-HERE.md` مسیر شروع هر چت Codex را مشخص می‌کند.
- `CONTEXT-MAP.md` مشخص می‌کند در هر وضعیت کدام فایل‌ها باید خوانده شوند.
- فایل‌های سنگین در `references/` هستند و فقط on-demand خوانده می‌شوند.
- تصمیم‌های رسمی در `canonical/` هستند.
- تصمیم‌های ناشی از پاسخ اپراتور در `decisions/` ثبت می‌شوند.
- AI Pack واحد اجرایی اصلی است و Taskها داخل خود Pack می‌آیند.
- پوشه جداگانه‌ای به نام `tasks/` وجود ندارد.

### مدل مالکیت مستندات

ساختار هدف چهار مرز مالکیت دارد:

1. `docs/ai/` برای قوانین، قالب‌ها، چرخه‌ها و مسیریابی مشترک و قابل استفادهٔ مجدد؛
2. `docs/project/` برای دانش سراسری repository، تحلیل سورس، موضوعات مشترک بین اجزا و رکوردهای چرخهٔ کاری خود پروژه؛
3. `<owner-root>/docs/ai/` برای مستندات مخصوص یک component مستقل و owned؛
4. `docs/project/` برای تحلیل componentهای third-party تا مستندات پروژه داخل سورس قابل جایگزینی آن‌ها قرار نگیرد.

مالکیت فقط از روی نام فایل، پیشوندی مثل `ND`، پوشهٔ فعلی، Release/Phase یا نام فناوری تعیین نمی‌شود. محتوای سند، scope، مخاطب، منبع حقیقت، مصرف‌کننده‌ها و وابستگی‌های آن باید بررسی شوند.

وضعیت فعلی انتقالی است: ورودی‌های مالکیت ساخته شده‌اند، اما corpus موجود هنوز جابه‌جا نشده است. تا اجرای Packهای بعدی، مسیرهای فعلی `docs/ai/` و سایر مسیرهای موجود معتبر می‌مانند و manifest هر owner مسیر فعلی و هدف را از هم جدا می‌کند.

پس از انتخاب owner، profile همان owner روی قوانین مشترک بارگذاری می‌شود. Profile مقدارها و محدودیت‌های مخصوص پروژه یا پلاگین را اضافه یا سخت‌گیرانه‌تر می‌کند، اما جایگزین Core نیست، gateهای مشترک را غیرفعال نمی‌کند و Canonical یا scope جاری Pack را override نمی‌کند.

- Profile پروژه: مسیری که entry پروژه اعلام می‌کند
- Profile component مستقل: مسیری که manifest همان owner اعلام می‌کند

---

## 2. نمای کلی ساختار

```text
AGENTS.md

docs/project/
  PROJECT-DOCS-INDEX.md

<owner-discovery-root>/
  <OWNERS-DOCS-INDEX>.md

<owned-component-root>/
  AGENTS.md
  docs/ai/
    README.md
    manifest.yaml

docs/ai/
  AI-DOCS-INDEX.md

  start/
    START-HERE.md
    CONTEXT-MAP.md
    AI-STRUCTURE-GUIDE.md

  rules/
    RULES-INDEX.md
    AI-EXECUTION-RULES.md
    OPERATOR-WORKFLOW.md
    SCOPE-CONTROL-RULES.md
    GIT-AND-COMMIT-RULES.md
    TEST-AND-VALIDATION-RULES.md
    COMMENTING-RULES.md
    QUESTION-ANSWER-DECISION-RULES.md
    CONFLICT-CHANGE-REMEDIATION-RULES.md
    REPORTING-RULES.md


  canonical/
    CANONICAL-INDEX.md
    GLOBAL-CANONICAL-DECISIONS.md
    release-{N}/
      RELEASE-{N}-CANONICAL-DECISIONS.md
      phases/
        PHASE-{X}-CANONICAL-DECISIONS.md

  guides/
    GUIDES-INDEX.md
    release-{N}/
      RELEASE-{N}-SUMMARY.md
      RELEASE-{N}-FULL-GUIDE.md
      phases/
        PHASE-{X}-SUMMARY.md

  references/
    REFERENCES-INDEX.md
    PROJECT-ANALYSIS-SUMMARY.md
    PROJECT-ANALYSIS-FULL.md
    SOURCE-DOCS-INDEX.md
    GRAPH-FILES-INDEX.md
    graphs/
    onboarding/
    source-docs/

  packs/
    PACKS-INDEX.md
    release-{N}/
      phase-{X}/

  runs/
    RUNS-INDEX.md
    release-{N}/
      phase-{X}/

  reviews/
    REVIEWS-INDEX.md
    release-{N}/
      phase-{X}/

  decisions/
    DECISIONS-INDEX.md
    release-{N}/
      RELEASE-{N}-DECISION-LOG.md
      phase-{X}/
        PHASE-{X}-DECISION-LOG.md

  changes/
    CHANGES-INDEX.md
    release-{N}/
      phase-{X}/

  remediations/
    REMEDIATIONS-INDEX.md
    release-{N}/
      phase-{X}/

  templates/
    TEMPLATES-INDEX.md
    AI-PACK-TEMPLATE.md
    RUN-REPORT-TEMPLATE.md
    REVIEW-TEMPLATE.md
    DECISION-RECORD-TEMPLATE.md
    CHANGE-REQUEST-TEMPLATE.md
    REMEDIATION-PACK-TEMPLATE.md
    AGENT-FINAL-REPORT-TEMPLATE.md
    CONSISTENCY-REVIEW-TEMPLATE.md
    DOCS-MAINTENANCE-REVIEW-TEMPLATE.md
    FINAL-REVIEW-TEMPLATE.md
```

این نمودار هم وضعیت فعلی و هم ریشه‌های هدف را نشان می‌دهد. وجود یک ریشهٔ هدف به این معنی نیست که Canonical، Pack، Run یا سایر corpusها از مسیر فعلی منتقل شده‌اند. برای وضعیت واقعی باید index و manifest همان owner خوانده شود.

---

## 3. `AGENTS.md`

### کاربرد

`AGENTS.md` نقطه ورود اصلی برای AI Agent است، اما نباید شامل تمام جزئیات پروژه باشد. این فایل فقط قوانین کلی، محدودیت‌های غیرقابل مذاکره و مسیر مراجعه به فایل‌های دیگر را مشخص می‌کند.

### چه چیزی داخل آن می‌آید؟

- نقش AI Agent
- قوانین کلی اجرای AI
- قانون Source of Truth
- قانون Scope Control
- قانون No Opportunistic Refactor
- قانون Startup Read
- ارجاع به `START-HERE.md` و `CONTEXT-MAP.md`
- ارجاع به فایل‌های rule تخصصی

### چه چیزی نباید داخل آن بیاید؟

- توضیح کامل Releaseها و Phaseها
- جزئیات کامل Packها
- گزارش اجرای Packها
- تحلیل کامل سورس
- محتوای سنگین و تکراری guideها

### چه زمانی آپدیت می‌شود؟

فقط وقتی قانون عمومی رفتار AI یا مسیر کلی خواندن فایل‌ها تغییر کند.

---

## 4. `docs/ai/start/`

این پوشه برای فایل‌های شروع و مسیریابی context است.

### `START-HERE.md`

فایل شروع هر چت Codex است.

تولید/آپدیت می‌شود وقتی:

- ترتیب خواندن فایل‌ها در شروع چت تغییر کند.
- سیاست context budget تغییر کند.
- دستور شروع Phase یا Release تغییر کند.

### `CONTEXT-MAP.md`

نقشه تصمیم‌گیری برای خواندن فایل‌ها است.

تولید/آپدیت می‌شود وقتی:

- مسیر جدیدی برای Release یا Phase اضافه شود.
- نوع جدیدی از وضعیت اجرایی ایجاد شود؛ مثل conflict، remediation، review، decision یا commenting.
- فایل‌های rule یا canonical جابه‌جا شوند.

---

## 5. `docs/ai/rules/`

این پوشه قوانین عمومی، تکرارشونده و قابل ارجاع را نگه می‌دارد. هدف این پوشه این است که قوانین عمومی داخل همه Packها تکرار نشوند.

### فایل‌ها

- `AI-EXECUTION-RULES.md`: قوانین اجرای Pack توسط AI
- `OPERATOR-WORKFLOW.md`: روند کار اپراتور قبل و بعد از اجرای Pack
- `SCOPE-CONTROL-RULES.md`: محدودیت scope، فایل‌های مجاز و ممنوع، و جلوگیری از تغییرات جانبی
- `GIT-AND-COMMIT-RULES.md`: قوانین branch، diff، commit و وضعیت git
- `TEST-AND-VALIDATION-RULES.md`: قوانین تست، validation و برخورد با test failure
- `COMMENTING-RULES.md`: قوانین کامنت‌گذاری و PHPDoc
- `QUESTION-ANSWER-DECISION-RULES.md`: طبقه‌بندی پاسخ‌های اپراتور و محل ثبت تصمیم‌ها
- `CONFLICT-CHANGE-REMEDIATION-RULES.md`: مدیریت conflict، Change Request و Remediation
- `REPORTING-RULES.md`: قانون گزارش نهایی Agent

### چه زمانی فایل جدید در `rules/` ساخته می‌شود؟

وقتی یک قانون عمومی وجود دارد که:

- در چند Pack تکرار می‌شود.
- مربوط به یک Task خاص نیست.
- باید برای همه Releaseها یا Phaseها قابل استفاده باشد.

### چه چیزی نباید در `rules/` بیاید؟

- تصمیم خاص یک Release یا Phase
- گزارش اجرای یک Pack
- جزئیات تحلیل سورس
- تصمیم موقت ناشی از پاسخ اپراتور

---

## 6. `<owner-docs-root>/canonical/`

این پوشه محل تصمیم‌های رسمی و معتبر پروژه است. canonical یعنی چیزی که به‌عنوان قرارداد رسمی Release، Phase یا کل پروژه پذیرفته شده است.

### فایل‌ها

- `CANONICAL-INDEX.md`: فهرست canonicalها و مسیر خواندن آن‌ها
- `GLOBAL-CANONICAL-DECISIONS.md`: تصمیم‌های رسمی کل پروژه
- `release-{n}/RELEASE-{n}-CANONICAL-DECISIONS.md`: تصمیم‌های رسمی هر Release
- `release-{n}/phases/PHASE-{x}-CANONICAL-DECISIONS.md`: تصمیم‌های رسمی هر Phase

### چه زمانی آپدیت می‌شود؟

- وقتی تصمیم رسمی معماری یا قرارداد اجرایی تغییر کند.
- وقتی پاسخ اپراتور تبدیل به تصمیم رسمی Release یا Phase شود.
- وقتی Change Request پذیرفته شود و نیاز به تغییر تصمیم رسمی داشته باشد.

### چه زمانی نباید آپدیت شود؟

- برای clarification ساده
- برای نکته اجرایی موقت
- برای گزارش اجرای Pack
- برای حدس AI بدون تأیید اپراتور

### قانون مهم

AI نباید فقط بر اساس یک جواب معمولی اپراتور canonical را آپدیت کند، مگر اینکه:

1. اپراتور صریحاً بگوید این یک تصمیم رسمی است، یا
2. Pack فعلی آپدیت canonical را در scope داشته باشد، یا
3. AI از اپراتور تأیید بگیرد و اپراتور تأیید کند.

---

## 7. `<owner-docs-root>/guides/`

این پوشه راهنماهای خلاصه و عملیاتی Release و Phase را نگه می‌دارد.

### تفاوت guide با canonical

- `canonical/` می‌گوید تصمیم رسمی چیست.
- `guides/` توضیح می‌دهد چطور آن تصمیم‌ها در Release یا Phase اجرا شوند.

### فایل‌ها

- `GUIDES-INDEX.md`: فهرست guideها
- `release-{n}/RELEASE-{n}-SUMMARY.md`: خلاصه اجرایی Release
- `release-{n}/RELEASE-{n}-FULL-GUIDE.md`: راهنمای کامل Release، فقط در صورت نیاز
- `release-{n}/phases/PHASE-{x}-SUMMARY.md`: خلاصه اجرایی Phase

### چه زمانی آپدیت می‌شود؟

- وقتی ساختار یا هدف اجرایی Release/Phase واضح‌تر شود.
- وقتی خلاصه Phase برای شروع چت Codex باید دقیق‌تر شود.
- وقتی راهنمای کامل نیاز به اصلاح داشته باشد.

### قانون context

در شروع چت، فقط summary خوانده شود. Full guide فقط وقتی خوانده شود که context summary کافی نباشد یا AI نیاز به توضیح عمیق‌تر داشته باشد.

---

## 8. `docs/project/references/`

این پوشه محل فایل‌های سنگین و مرجع است. این فایل‌ها برای شروع هر چت خوانده نمی‌شوند.

### فایل‌ها و پوشه‌ها

- `REFERENCES-INDEX.md`: فهرست منابع مرجع
- `PROJECT-ANALYSIS-SUMMARY.md`: خلاصه تحلیل سامانه، قابل خواندن در شروع Phase در صورت نیاز
- `PROJECT-ANALYSIS-FULL.md`: تحلیل کامل سامانه، فقط on-demand
- `SOURCE-DOCS-INDEX.md`: فهرست مستندات شرکت یا سورس اصلی
- `GRAPH-FILES-INDEX.md`: فهرست فایل‌های graph
- `graphs/`: فایل‌های JSON گرافی
- `onboarding/`: فایل‌های onboarding تولیدشده از ابزار graph/understanding
- `source-docs/`: مستندات رسمی سورس یا شرکت

### چه زمانی خوانده می‌شود؟

- وقتی AI برای اجرای Pack به درک عمیق‌تر از سورس نیاز دارد.
- وقتی canonical یا guide کافی نیست.
- وقتی conflict بین source code و مستندات وجود دارد.
- وقتی AI به graph یا source docs نیاز واقعی دارد.

### چه زمانی آپدیت می‌شود؟

- وقتی تحلیل جدیدی از سورس تولید شود.
- وقتی graphها دوباره تولید شوند.
- وقتی مستندات رسمی سورس اضافه یا جایگزین شوند.

---

## 9. `<owner-docs-root>/packs/`

این پوشه محل AI Packها است. AI Pack واحد اجرایی اصلی است که به AI داده می‌شود.

### تصمیم قطعی ساختار

پوشه جداگانه‌ای به نام `tasks/` ساخته نمی‌شود.

```text
AI Pack = واحد اجرایی قابل تحویل به AI
Task = زیرکار داخل همان AI Pack
```

یک Pack می‌تواند یک Task یا چند Task مرتبط داشته باشد.

### فایل‌ها

- `PACKS-INDEX.md`: فهرست Packها
- `release-{n}/phase-{x}/AI-PACK-...md`: Packهای اجرایی هر Phase

### ساختار استاندارد هر AI Pack

هر AI Pack باید این بخش‌ها را داشته باشد:

1. Task ID
2. Task Title
3. Goal
4. Context
5. Related Release / Phase
6. Related Epic / Feature / Story
7. Source References
8. Files to Create
9. Files to Edit
10. Files to Read / Reference
11. Do Not Change
12. Clarification Questions Before Implementation
13. Implementation Rules
14. Architecture Constraints
15. Validation Rules
16. Security Rules
17. Data / Migration Rules
18. Commenting Requirements
19. Testing Requirements
20. Acceptance Checklist
21. Tests to Add
22. Tests to Run
23. Expected Output
24. Operator Execution Checklist
25. Agent Final Report
26. Review Checklist
27. Rollback / Safety Notes
28. Stop Conditions
29. Open Questions

### چه زمانی Pack تولید می‌شود؟

- وقتی یک کار اجرایی مشخص باید توسط AI انجام شود.
- وقتی scope، فایل‌های مجاز، تست‌ها و خروجی مورد انتظار قابل تعریف باشد.
- وقتی کار آن‌قدر مهم است که باید گزارش، review و commit جدا داشته باشد.

### چه چیزی نباید داخل Pack تکرار شود؟

قوانین عمومی و ثابت نباید مفصل داخل هر Pack تکرار شوند. Pack باید به فایل‌های `rules/` ارجاع دهد و فقط قوانین خاص همان Pack را بنویسد.

---

## 10. `<owner-docs-root>/runs/`

این پوشه محل گزارش اجرای واقعی Packها است.

### کاربرد

Run Report نشان می‌دهد یک Pack واقعاً چطور اجرا شده است، چه فایل‌هایی تغییر کرده‌اند، چه تست‌هایی اجرا شده‌اند، چه چیزی موفق بوده، چه چیزی fail شده، و اپراتور چه reviewای انجام داده است.

### فایل‌ها

- `RUNS-INDEX.md`: فهرست اجرای Packها
- `release-{n}/phase-{x}/...`: گزارش اجرای Packهای همان Phase

### چه زمانی تولید می‌شود؟

بعد از اجرای هر AI Pack.

### چه چیزی داخل آن ثبت می‌شود؟

- Pack ID
- تاریخ اجرا
- branch
- فایل‌های تغییرکرده
- تست‌های اجراشده
- نتیجه تست‌ها
- خلاصه Agent Final Report
- پاسخ‌های مهم اپراتور در همان اجرا
- وضعیت نهایی: accepted، rejected، needs-fix، superseded

---

## 11. `<owner-docs-root>/reviews/`

این پوشه محل reviewهای انسانی، phase review و release review است.

### فایل‌ها

- `REVIEWS-INDEX.md`: فهرست reviewها
- `release-{n}/phase-{x}/...`: reviewهای مربوط به همان Phase

### چه زمانی تولید می‌شود؟

- بعد از پایان یک Pack مهم
- بعد از پایان یک Phase
- بعد از پایان یک Release
- وقتی اپراتور نیاز به بازبینی تصمیم‌ها، تست‌ها یا conflictها داشته باشد

### تفاوت Review با Run

- `runs/` گزارش اجرای واقعی یک Pack است.
- `reviews/` ارزیابی انسانی یا دوره‌ای از خروجی‌ها و تصمیم‌ها است.

---

## 12. `<owner-docs-root>/decisions/`

این پوشه محل ثبت تصمیم‌هایی است که ممکن است از سؤال و جواب اپراتور، اجرای Pack یا review به‌وجود بیایند.

### کاربرد

همه جواب‌های اپراتور نباید وارد canonical شوند. ابتدا باید طبقه‌بندی شوند:

1. Clarification
2. Implementation Note
3. Pack-Local Decision
4. Phase/Release Decision
5. Temporary Decision / Revisit Later
6. Change Request

### فایل‌ها

- `DECISIONS-INDEX.md`: فهرست decision logها
- `release-{n}/RELEASE-{n}-DECISION-LOG.md`: تصمیم‌های سطح Release
- `release-{n}/phase-{x}/PHASE-{x}-DECISION-LOG.md`: تصمیم‌های سطح Phase

### چه زمانی آپدیت می‌شود؟

- وقتی جواب اپراتور فقط clarification ساده نیست.
- وقتی تصمیمی روی Packهای بعدی اثر دارد.
- وقتی تصمیم موقت باید در Release بعدی بررسی شود.
- وقتی تصمیم هنوز canonical نیست، ولی باید trace شود.

### رابطه با canonical

اگر تصمیم رسمی شد، باید هم در `decisions/` ثبت شود و هم در canonical مربوطه اعمال شود.

---

## 13. `<owner-docs-root>/changes/`

این پوشه محل Change Requestها است.

### کاربرد

وقتی تصمیم یا پیاده‌سازی قبلی باید تغییر کند، ولی تغییر ساده و محلی نیست، باید Change Request ثبت شود.

### فایل‌ها

- `CHANGES-INDEX.md`: فهرست Change Requestها
- `release-{n}/phase-{x}/...`: Change Requestهای مربوط به همان Phase

### چه زمانی تولید می‌شود؟

- وقتی canonical فعلی باید تغییر کند.
- وقتی اجرای یک Pack نشان دهد تصمیم قبلی مشکل دارد.
- وقتی اپراتور تصمیم جدیدی بدهد که اثر آن از Pack فعلی فراتر می‌رود.
- وقتی conflict مهم بین code، canonical، guide یا Pack پیدا شود.

---

## 14. `<owner-docs-root>/remediations/`

این پوشه محل Packهای اصلاحی بعد از Change Request است.

### کاربرد

Pack اصلی نباید بعد از اجرا دستکاری شود. اگر نیاز به اصلاح وجود دارد، باید Remediation Pack ساخته شود.

### فایل‌ها

- `REMEDIATIONS-INDEX.md`: فهرست remediationها
- `release-{n}/phase-{x}/...`: Remediation Packهای مربوط به همان Phase

### چه زمانی تولید می‌شود؟

- وقتی Change Request پذیرفته شود.
- وقتی یک Pack اجرا شده و بعداً نیاز به اصلاح دارد.
- وقتی باید بدون دستکاری Pack اصلی، اصلاح کنترل‌شده انجام شود.

---

## 15. `docs/ai/templates/`

این پوشه قالب‌های استاندارد را نگه می‌دارد.

### فایل‌ها

- `TEMPLATES-INDEX.md`
- `AI-PACK-TEMPLATE.md`
- `RUN-REPORT-TEMPLATE.md`
- `REVIEW-TEMPLATE.md`
- `DECISION-RECORD-TEMPLATE.md`
- `CHANGE-REQUEST-TEMPLATE.md`
- `REMEDIATION-PACK-TEMPLATE.md`
- `AGENT-FINAL-REPORT-TEMPLATE.md`

### چه زمانی آپدیت می‌شود؟

وقتی قالب رسمی Pack، Run Report، Review، Decision، Change یا Remediation تغییر کند.

### قانون

اگر قالب تغییر کند، باید index مربوطه و در صورت نیاز `CONTEXT-MAP.md` هم بررسی شود.

---

## 16. روند تولید فایل جدید

وقتی قرار است فایل جدیدی ایجاد شود، ابتدا owner از روی محتوای واقعی تعیین می‌شود و سپس نوع فایل مشخص می‌شود:

| مالک محتوا | ریشهٔ هدف |
|---|---|
| قانون، قالب یا چرخهٔ reusable | `docs/ai/` |
| repository یا چند component | `docs/project/` |
| یک component مستقل و owned | ریشهٔ مستندات اعلام‌شده در manifest همان owner |
| تحلیل component third-party | `docs/project/`، نه پوشهٔ سورس third-party |

در دورهٔ انتقال، ایجاد فایل جدید باید علاوه بر ریشهٔ هدف، `migration_status` و مسیرهای فعلی را هم رعایت کند. فایل جدید نباید به‌تنهایی یک مسیر هدفِ ساخته‌نشده را authoritative اعلام کند.

پس از انتخاب owner، نوع فایل تعیین می‌شود:

| نوع محتوا | محل ایجاد |
|---|---|
| قانون عمومی AI یا اپراتور | `docs/ai/rules/` |
| تصمیم رسمی پروژه | `<owner-docs-root>/canonical/` |
| راهنمای اجرایی Release/Phase | `<owner-docs-root>/guides/` |
| تحلیل سنگین یا مرجع پروژه | `docs/project/references/` |
| Pack اجرایی | `<owner-docs-root>/packs/` |
| گزارش اجرای Pack | `<owner-docs-root>/runs/` |
| Review انسانی یا دوره‌ای | `<owner-docs-root>/reviews/` |
| تصمیم ناشی از پاسخ اپراتور | `<owner-docs-root>/decisions/` |
| درخواست تغییر | `<owner-docs-root>/changes/` |
| Pack اصلاحی | `<owner-docs-root>/remediations/` |
| قالب | `docs/ai/templates/` |

بعد از ایجاد فایل جدید، index همان پوشه باید آپدیت شود.

---

## 17. قانون آپدیت indexها

هر پوشه عملیاتی باید index داشته باشد. وقتی فایل جدیدی اضافه می‌شود، index همان بخش باید آپدیت شود.

مثال‌ها:

- Pack جدید → آپدیت `PACKS-INDEX.md`
- Run Report جدید → آپدیت `RUNS-INDEX.md`
- Review جدید → آپدیت `REVIEWS-INDEX.md`
- Decision جدید → آپدیت `DECISIONS-INDEX.md`
- Change Request جدید → آپدیت `CHANGES-INDEX.md`
- Remediation جدید → آپدیت `REMEDIATIONS-INDEX.md`
- Canonical جدید → آپدیت `CANONICAL-INDEX.md`
- Reference جدید → آپدیت `REFERENCES-INDEX.md`

---

## 18. قانون خواندن فایل‌ها برای AI

AI نباید در شروع چت همه فایل‌ها را بخواند.

شروع چت فقط با این فایل‌ها انجام می‌شود:

1. `AGENTS.md`
2. `docs/ai/start/START-HERE.md`
3. `docs/ai/start/CONTEXT-MAP.md`

بعد از آن، AI فقط فایل‌هایی را می‌خواند که `CONTEXT-MAP.md` برای وضعیت فعلی مشخص کرده است.

---

## 19. قانون حفظ Packهای اصلی

Packهای اصلی بعد از اجرا نباید برای اصلاح گذشته دستکاری شوند.

اگر بعد از اجرا نیاز به تغییر وجود داشت:

1. موضوع در Run Report یا Review ثبت می‌شود.
2. اگر تغییر مهم است، Change Request ساخته می‌شود.
3. اگر Change Request پذیرفته شد، Remediation Pack ساخته می‌شود.
4. Pack اصلی به‌عنوان تاریخچه حفظ می‌شود.

---

## 20. قانون تصمیم‌های موقت

اگر اپراتور تصمیمی بدهد که فقط برای Release فعلی معتبر است و باید در Release بعدی بررسی شود، آن تصمیم باید در `decisions/` ثبت شود با این مشخصات:

```text
Status: Temporary
Scope: Release X یا Phase Y
Review At: Release N Planning
```

اگر تصمیم رسمی برای Release فعلی است، canonical همان Release هم باید بعد از تأیید اپراتور آپدیت شود.

---

## 21. قانون کامنت‌گذاری کد

قانون کامل کامنت‌گذاری در این فایل است:

```text
docs/ai/rules/COMMENTING-RULES.md
```

خلاصه تصمیم:

- قرارداد کامنت مستندسازِ زبان و فناوری در Profile مالک مربوطه تعیین می‌شود و باید هدفمند استفاده شود.
- کامنت نباید چیزی را توضیح دهد که خود کد واضح می‌گوید.
- کامنت باید چرایی، business rule، invariant، محدودیت غیر واضح یا trade-off را توضیح دهد.
- نیاز خاص هر Pack در بخش `Commenting Requirements` همان Pack نوشته می‌شود.

---

## 22. قانون پاسخ‌های اپراتور

قانون کامل در این فایل است:

```text
docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md
```

هر پاسخ اپراتور باید طبقه‌بندی شود:

- Clarification
- Implementation Note
- Pack-Local Decision
- Phase/Release Decision
- Temporary Decision / Revisit Later
- Change Request

محل ثبت پاسخ بر اساس طبقه‌بندی تعیین می‌شود.

---

## 23. چه زمانی این فایل خوانده شود؟

این فایل در شروع هر چت Codex اجباری نیست.

این فایل را بخوان وقتی:

- می‌خواهی ساختار `docs/ai` را بفهمی.
- می‌خواهی فایل جدید بسازی و نمی‌دانی کجا باید قرار بگیرد.
- می‌خواهی بدانی هر پوشه چه مسئولیتی دارد.
- می‌خواهی نحوه تولید و آپدیت فایل‌ها را بررسی کنی.
- در حال onboarding اپراتور یا برنامه‌نویس جدید هستی.

---

## 24. خلاصه نهایی

ساختار جدید برای این ساخته شده است که:

```text
AI کمتر بخواند، دقیق‌تر بخواند، و تصمیم‌ها گم نشوند.
```

نقش فایل‌های اصلی:

```text
AGENTS.md = قوانین کلی و Router
START-HERE.md = شروع چت Codex
CONTEXT-MAP.md = انتخاب فایل بر اساس وضعیت
STRUCTURE-GUIDE.md = توضیح انسانی ساختار و نحوه تولید فایل‌ها
rules/ = قوانین عمومی
canonical/ = تصمیم‌های رسمی
guides/ = راهنمای اجرایی Release/Phase
references/ = منابع سنگین و on-demand
packs/ = بسته‌های اجرایی AI
runs/ = گزارش اجرای Packها
reviews/ = بازبینی‌ها
decisions/ = تصمیم‌های ناشی از پاسخ اپراتور یا اجرای Pack
changes/ = درخواست‌های تغییر
remediations/ = Packهای اصلاحی
templates/ = قالب‌ها
```
