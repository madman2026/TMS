# جریان کاری اپراتور

## هدف

این فایل جریان کاری عملی‌ای را تعریف می‌کند که اپراتور انسانی هنگام کار با AI Agent باید دنبال کند.

این فایل راهنمای اپراتور است.

این فایل به اپراتور می‌گوید:

- چه زمانی چت جدید شروع کند
- از Agent بخواهد چه فایل‌هایی را بخواند
- چطور یک Pack را شروع کند
- چطور خروجی Agent را بررسی کند
- چه زمانی Run Report درخواست کند
- چه زمانی commit انجام دهد
- چه زمانی Review رسمی درخواست کند
- وقتی conflict یا Pack مسدود شده رخ داد چه کند
- در پایان Phase یا Release چه کند

این فایل جایگزین فایل‌های قانون جزئی نیست.

رفتارهای جزئی همچنان مالکیتشان با فایل‌های rule مربوطه است که از طریق این فایل انتخاب می‌شوند:

```text
docs/ai/rules/RULES-INDEX.md
```

---

## 1. چت جدید برای هر Phase

برای هر Phase یک چت جدید Codex باز کن.

در ابتدای چت، اپراتور باید از Agent بخواهد:

1. branch فعلی git را بررسی کند
2. git status را بررسی کند
3. git branch --show-current را بررسی کند
4. هیچ فایلی را تغییر ندهد
5. اول فقط فایل‌های startup الزامی را بخواند
6. برای فایل‌های بعدی از context map پیروی کند
7. profileهای project و owner اعلام‌شده توسط route یا manifest انتخاب‌شده را بارگذاری کند
8. قبل از دریافت اولین Pack، یک خلاصه کوتاه از context بدهد

نمونه prompt پیشنهادی اپراتور:

```text
Start a new Phase execution context.

First, check git branch and git status without changing files.

Then read only:

- AGENTS.md
- docs/ai/start/START-HERE.md
- docs/ai/start/CONTEXT-MAP.md

After that, use the context map to identify the minimum required files for the current Release and Phase, and load the project and owner profiles declared by the selected route or owner manifest.

Do not read unrelated Releases, Phases, Runs, Reviews, or reference files unless required.

Give me a short context summary before I provide the next AI Pack.
```

---

## 2. چت ادامه‌ای داخل همان Phase

یک Phase می‌تواند بیش از یک چت Codex داشته باشد، مخصوصاً وقتی Phase بزرگ، طولانی، حساس یا از نظر context سنگین باشد.

از چت ادامه‌ای استفاده کن وقتی:

* چند Pack در همان Phase اجرا شده‌اند
* context چت بیش از حد طولانی شده است
* Agent شروع کرده فرض‌های قدیمی و فعلی را با هم قاطی کند
* Pack بعدی حساس است
* یک conflict، Change Request یا Remediation باعث تغییر context همان Phase شده است
* چند روز از اجرای Pack قبلی گذشته است
* اپراتور می‌خواهد context اجرایی تمیز داشته باشد، بدون اینکه از Phase فعلی خارج شود

چت ادامه‌ای، Phase جدید نیست.

این چت باید همان Release و همان Phase را با context تازه و حداقلی ادامه دهد.

### قانون نهایی محدوده چت‌های Codex

واحد مدیریتی اجرای پروژه، Phase است؛ اما واحد عملی اجرای چت می‌تواند بر اساس اندازه و حساسیت Phase تغییر کند.

برای Phaseهای کوچک، یک چت Codex می‌تواند برای کل Phase کافی باشد.

برای Phaseهای بزرگ، طولانی یا context-heavy، اجرای Phase را به چند چت ادامه‌ای داخل همان Phase تقسیم کن.

برای Phaseهای بزرگ، پیشنهاد پیش‌فرض این است که هر 4 تا 6 Pack یک چت ادامه‌ای جدید باز شود، یا زودتر اگر context سنگین شد.

برای Packهای حساس، حتی اگر کمتر از 4 Pack اجرا شده باشد، یک چت ادامه‌ای جدید باز کن.

برای هر Pack چت جداگانه باز نکن، مگر اینکه یکی از این شرایط وجود داشته باشد:

* Pack حساس باشد
* Pack مربوط به migration، security، provider، callback، status model، permission، queue یا contract مهم باشد
* context چت قبلی بیش از حد طولانی شده باشد
* Agent شروع کرده فرض‌های قبلی و فعلی را قاطی کند
* conflict، Change Request یا Remediation مهم رخ داده باشد
* چند روز از اجرای Pack قبلی گذشته باشد
* اپراتور بخواهد اجرای Pack بعدی با context تمیز انجام شود

استفاده از یک چت واحد برای کل Release توصیه نمی‌شود، چون Release معمولاً چند Phase و تصمیم‌های زیادی دارد و context آن خیلی زود سنگین و مبهم می‌شود.

در چت ادامه‌ای، Agent باید فقط context حداقلی و مرتبط با همان Release، همان Phase و Pack فعلی را بخواند و profileهای project و owner اعلام‌شده توسط context map یا owner manifest را نیز بارگذاری کند. Agent نباید همه Packها، همه Runها، همه Reviewها، فایل‌های Phase نامرتبط، graph fileها یا full project analysis را بخواند.

### Prompt پیشنهادی اپراتور

```text id="mb1fr2"
This is a continuation chat inside the same Release and Phase.

Do not treat this as a new Phase.

First, check git branch and git status without changing files.

Then read only:

- AGENTS.md
- docs/ai/start/START-HERE.md
- docs/ai/start/CONTEXT-MAP.md
- <owner-docs-root>/canonical/CANONICAL-INDEX.md
- <owner-docs-root>/guides/GUIDES-INDEX.md
- current Release canonical file
- current Phase canonical file
- current Release summary guide
- current Phase summary guide
- <owner-docs-root>/packs/PACKS-INDEX.md
- <owner-docs-root>/runs/RUNS-INDEX.md only to locate latest relevant Run Reports
- latest relevant accepted or executed Run Reports only if needed
- the current AI Pack only

Do not read all Packs, all Runs, all Reviews, unrelated Phase files, graph files, or full project analysis.

After reading the minimum required context, summarize:

- current Release
- current Phase
- last known accepted/executed Pack
- current Pack to execute
- relevant open follow-ups, decisions, changes, remediations, or blockers
- files you need before implementation

Do not start implementation until I provide or confirm the current Pack.
```

---

## 3. قبل از هر Pack

قبل از اینکه اپراتور اجازه اجرای Pack را بدهد، باید Pack فعلی را به Agent بدهد یا تأیید کند و از Agent بخواهد gate قبل از اجرا را انجام دهد.

Agent نباید بلافاصله بعد از دریافت Pack شروع به تغییر فایل‌ها کند.

Agent باید ابتدا این موارد را خلاصه کند:

* هدف Pack
* محدوده اجرا
* فایل‌هایی که می‌تواند ایجاد کند
* فایل‌هایی که می‌تواند ویرایش کند
* فایل‌هایی که نباید تغییر دهد
* تست‌هایی که باید اضافه شوند
* تست‌هایی که باید اجرا شوند
* stop conditionها
* هر ابهام یا دستور نامشخص

سپس Agent باید gate قبل از اجرا را طبق فایل زیر انجام دهد:

```text
docs/ai/rules/AI-EXECUTION-RULES.md
```

gate قبل از اجرا شامل این موارد است:

* بررسی `git status`
* بررسی branch فعلی
* گزارش نتیجه در چت
* تکرار بخش زیر از Pack فعلی:

  * `## 24. Operator Execution Checklist`
  * `### Before AI Execution`

اپراتور باید خروجی gate قبل از اجرا را بررسی کند و فقط در صورت تأیید، اجازه اجرای Pack را بدهد.

نمونه prompt پیشنهادی اپراتور:

```text
Here is the next AI Pack.

Before implementation:

1. Summarize the Pack scope.
2. Check git status and current branch.
3. Repeat the Pack `Before AI Execution` checklist.
4. Report the result in chat.
5. Wait for my approval before changing files.

Do not start implementation until I approve.
```

اگر Agent سؤال شفاف‌سازی پرسید، اپراتور باید پاسخ روشن بدهد و اگر پاسخ روی scope، decisionها، Canonical files، Change Requestها یا Remediation اثر دارد، از Agent بخواهد پاسخ را classify کند.

---

## 4. هنگام اجرای Pack

هنگام اجرای Pack، اپراتور نباید از Agent بخواهد بخش‌های نامرتبط را فرصت‌طلبانه بهتر کند.

اگر Agent مسئله‌ای خارج از scope Pack کشف کرد، اپراتور باید از Agent بخواهد:

1. اگر مسئله روی safety، architecture، contracts، data، tests یا accepted behavior اثر دارد، متوقف شود
2. مسئله را گزارش کند
3. مشخص کند مسئله clarification، decision، conflict، Change Request یا Remediation trigger است
4. فقط وقتی ادامه دهد که مسئله داخل scope Pack باشد یا اپراتور مسیر بعدی را صریحاً تأیید کند

Agent نباید scope را بی‌سروصدا گسترش دهد.

---

## 5. پاسخ‌ها و تصمیم‌های اپراتور

وقتی اپراتور به یک سؤال پاسخ می‌دهد، اگر پاسخ ممکن است روی موارد زیر اثر بگذارد، باید از Agent بخواهد پاسخ را classify کند:

- implementation scope
- architecture
- contracts
- Canonical files
- future Packs
- Change Requests
- Remediation Packs
- رفتار release یا phase

اپراتور لازم نیست برای هر پاسخ یک Review رسمی بسازد.

مسیر معمول decision:

```text
Operator answer
→ classification
→ clarification or implementation note
→ decision if needed
→ Canonical update if official and allowed
→ Change Request if accepted contract must change
→ Remediation Pack if implementation correction is required
```

classification تصمیم‌ها مالکیتش با decision rules است، نه این فایل.

---

## 6. بعد از هر Pack

بعد از اجرای Pack، Agent نباید بلافاصله Agent Final Report تولید کند.

ابتدا Agent باید gate بعد از اجرا را طبق فایل زیر انجام دهد:

```text
docs/ai/rules/AI-EXECUTION-RULES.md
```

gate بعد از اجرا شامل این موارد است:

* بررسی `git status`
* بررسی `git diff --name-only`
* مقایسه فایل‌های تغییرکرده با:

  * `Files to Create`
  * `Files to Edit`
  * governance یا maintenance updates مجاز
  * فایل‌هایی که اپراتور صریحاً تأیید کرده است
* دسته‌بندی فایل‌های تغییرکرده به:

  * تغییرات مجاز پیاده‌سازی Pack
  * تغییرات مجاز governance یا maintenance
  * تغییرات غیرمجاز خارج از scope
* گزارش نتیجه در چت
* تکرار بخش زیر از Pack فعلی:

  * `## 24. Operator Execution Checklist`
  * `### After AI Execution`

اپراتور باید خروجی gate بعد از اجرا را بررسی کند و فقط بعد از تأیید اپراتور، Agent می‌تواند Agent Final Report را تولید کند.

اگر اپراتور درخواست fix، revert، بررسی اضافه یا اصلاح تست داد، Agent باید فقط همان اقدام تأییدشده و داخل scope را انجام دهد و بعد دوباره gate بعد از اجرا را تکرار کند.

فقط بعد از تأیید gate بعد از اجرا، Agent باید Agent Final Report را تولید کند.

نمونه prompt پیشنهادی اپراتور:

```text
Before producing the Agent Final Report:

1. Check git status.
2. Check git diff --name-only.
3. Compare changed files with Files to Create and Files to Edit.
4. Classify changed files as allowed implementation, allowed governance/maintenance, or unauthorized out-of-scope.
5. Repeat the Pack `After AI Execution` checklist.
6. Report the result in chat.
7. Wait for my approval before producing the Agent Final Report.
```

بعد از تولید Agent Final Report، اپراتور باید این موارد را بررسی کند:

1. Agent Final Report
2. فایل‌های تغییرکرده
3. scope compliance
4. تست‌های اضافه‌شده
5. تست‌های اجراشده
6. تست‌های fail شده یا skipped
7. بخش human review
8. documentation/index/canonical follow-up updates
9. نیاز به decision/change/remediation
10. تغییرات غیرمجاز فایل‌ها، اگر وجود داشته باشد

فقط به این دلیل که Agent گفته task کامل شده، commit نکن.

اپراتور باید نتیجه را validate کند.

---

## 7. Run Report قبل از Commit

برای کار accepted Pack که باید به‌عنوان execution history نگه داشته شود، Run Report باید قبل از commit ساخته یا بررسی شود.

flow معمول:

```text
Pack execution
→ Post-execution operator gate
→ Operator approval
→ Agent Final Report
→ Operator Check
→ Run Report
→ Commit
```

قبل از commit، اپراتور باید بررسی کند:

- Agent Final Report کامل است
- اگر Run Report لازم است، وجود دارد
- git status قابل انتظار است
- git diff بررسی شده است
- تست‌ها کامل‌اند یا تست‌های skipped توضیح داده شده‌اند
- هیچ فایل غیرمجازی تغییر نکرده است
- documentation/index updates لازم انجام شده یا به‌عنوان follow-up ثبت شده است
- decisionهای لازم ثبت شده‌اند
- Canonical updateهای لازم تأیید شده‌اند
- Change Requestها یا Remediation Packهای لازم رسیدگی شده‌اند
- اگر Pack رفتار API را تغییر داده، اثر آن روی Postman Collection بررسی شده است
- اگر لازم بوده، Postman Collection به‌روزرسانی شده است
- اگر لازم بوده، Postman Environment فقط با placeholderهای امن به‌روزرسانی شده است
- آپدیت‌های Postman، آپدیت‌های انجام‌نشده، یا follow-upهای لازم در Run Report ثبت شده‌اند


نمونه prompt پیشنهادی اپراتور:

```text
Create the Run Report for this accepted Pack before commit.

Make sure it includes:

- Pack ID
- execution summary
- changed files
- tests run
- test results
- Postman Collection impact
- Postman Collection updates, if API behavior changed
- scope compliance
- documentation maintenance
- decisions/canonical/change/remediation notes
- required follow-up updates
```

قبل از کامل‌شدن Run Report و validation checks، کار completed Pack را commit نکن.

---

## 8. آمادگی برای Commit

اپراتور فقط وقتی باید commit کند که:

1. خروجی Pack پذیرفته شده باشد
2. Run Report ساخته یا بررسی شده باشد
3. اگر Pack رفتار API را تغییر داده، اثر آن روی Postman Collection بررسی شده و در صورت نیاز به‌روزرسانی شده باشد
4. تست‌ها pass شده باشند یا skipped tests صریحاً توجیه شده باشند
5. git diff بررسی شده باشد
6. scope compliance تأیید شده باشد
7. تغییرات غیرمجاز revert شده باشند
8. follow-upهای لازم یا انجام شده باشند یا ثبت شده باشند
9. هیچ conflict مسدودکننده‌ای حل‌نشده باقی نمانده باشد

نمونه prompt پیشنهادی اپراتور:

```text
Check whether this work is ready to commit.

Confirm:

- branch
- git status
- diff summary
- tests
- scope compliance
- Run Report status
- Postman Collection status, if API behavior changed
- unresolved blockers
```

قوانین commit مالکیتشان با git rules و operator workflow rules است که از طریق `RULES-INDEX.md` انتخاب می‌شوند.

---

## 9. تست‌های Fail شده یا مشکلات Validation

اگر یک test fail شد، اپراتور نباید از Agent بخواهد کل Pack را بازنویسی کند.

اپراتور باید از Agent بخواهد:

1. تست fail شده را شناسایی کند
2. علت احتمالی را توضیح دهد
3. کوچک‌ترین fix لازم را مشخص کند
4. تأیید کند fix داخل scope Pack است
5. فقط همان failure را fix کند
6. تست مرتبط را دوباره اجرا کند
7. final report یا Run Report را با failure اولیه و نتیجه نهایی آپدیت کند

نمونه prompt پیشنهادی اپراتور:

```text
A test failed.

Do not rewrite the whole Pack.

Identify the failing test, likely cause, affected file, and smallest fix.

Confirm the fix is inside Pack scope before changing files.
```

---

## 10. تغییرات غیرمجاز یا خارج از Scope

اگر یک فایل غیرمجاز تغییر کرد، اپراتور باید از Agent بخواهد:

1. فایل غیرمجاز را شناسایی کند
2. توضیح دهد چرا تغییر کرده است
3. تأیید کند آیا تغییر لازم است یا نه
4. اگر تأیید نشده، فقط همان تغییر غیرمجاز را revert کند
5. scope issue را در Agent Final Report یا Run Report گزارش کند

نمونه prompt پیشنهادی اپراتور:

```text
This file appears to be outside Pack scope:

<file path>

Explain why it changed.

If it is not explicitly allowed, revert only this unauthorized change.
```

اگر تغییر خارج از scope نشان‌دهنده یک تغییر محصولی یا معماری لازم باشد، آن را بی‌سروصدا نگه ندار.

اگر لازم است، از conflict/change/remediation workflow استفاده کن.

---

## 11. Conflict یا Pack مسدود شده

اگر conflict، تغییر accepted contract، cross-phase impact، Canonical/code mismatch یا unsafe partial implementation کشف شد، اپراتور نباید از Agent بخواهد Pack را به شکل عادی ادامه دهد.

Pack مسدود شده نباید به‌عنوان کار موفق commit شود.

flow مورد انتظار:

```text
Conflict found
→ Pack blocked
→ partial changes inspected
→ unsafe partial changes reverted
→ Decision Record if required
→ Change Request if required
→ Remediation Pack if required
→ Remediation execution
→ Run Report
→ Review if required
→ Commit remediation after validation
→ normalize or restart blocked Pack
```

نمونه prompt پیشنهادی اپراتور:

```text
Stop normal Pack execution.

Classify this as a conflict or blocked Pack if applicable.

Report:

- conflict type
- conflicting sources
- affected Pack
- partial changes
- whether revert is required
- whether Decision Record is required
- whether Change Request is required
- whether Remediation Pack is required
- whether Canonical update is required
- recommended next action
```

اگر conflict فقط نیاز به correction در Canonical دارد و نیازی به اصلاح code یا Pack ندارد، Remediation Pack غیرضروری نساز.

اگر accepted behavior یا accepted contract تغییر می‌کند، ممکن است Change Request همچنان لازم باشد حتی اگر Remediation Pack لازم نباشد.

---

## 12. چه زمانی Review رسمی درخواست شود

بعد از هر Pack عادی Review رسمی درخواست نکن.

Review رسمی فقط در checkpointهای رسمی یا زمانی که ریسک وجود دارد درخواست شود.

Review رسمی باید برای این موارد درخواست شود:

- اجرای Pack حساس
- Remediation حساس
- پایان Phase
- پایان Release
- consistency check بین Packs، Runs، Decisions و Canonical files
- اثر source/vendor/dependency update
- maintenance یا restructuring مهم در docs/ai
- formal validation که اپراتور صریحاً درخواست می‌کند

Review رسمی با operator check عادی فرق دارد.

operator check عادی بعد از هر Pack انجام می‌شود.

Review رسمی یک review record پایدار ایجاد می‌کند.

---

## 13. Review برای Pack حساس

وقتی Pack روی رفتار حساس یا پرریسک اثر دارد، Review رسمی درخواست کن.

نمونه‌های Pack حساس:

- API contracts
- database schema یا migrations
- status model
- provider adapter behavior
- callback processing
- security یا permissions
- queue/job/retry/dead-letter behavior
- rate limit، quota یا cost behavior
- idempotency
- cross-phase contracts
- Canonical decisions

نمونه prompt پیشنهادی اپراتور:

```text
Create a formal Review for this sensitive Pack.

Check:

- scope compliance
- changed files
- tests
- contract impact
- Canonical impact
- security or data impact
- unresolved decisions
- whether the Pack is accepted, rejected, or needs remediation
```

---

## 14. Review برای Remediation حساس

بعد از هر Remediation کوچک Review رسمی درخواست نکن.

بعد از Remediation فقط وقتی Review رسمی درخواست کن که Remediation این موارد را تغییر دهد یا روی آن‌ها اثر بگذارد:

- accepted contracts
- Canonical decisions
- API request یا response
- schema یا migration
- status behavior
- callback/provider behavior
- security یا permissions
- queue/retry/dead-letter behavior
- cross-phase behavior
- behavior موردنیاز Change Request

نمونه prompt پیشنهادی اپراتور:

```text
Create a Remediation Review.

Check whether the Remediation:

- fully satisfies the Change Request
- updates the accepted contract correctly
- updates Canonical files if required
- includes required tests
- does not introduce unrelated changes
- allows the blocked Pack to continue, normalize, regenerate, or be superseded
```

---

## 15. Consistency Review

Consistency Review زمانی مفید است که ممکن است بین چند Pack، Run، Decision یا Canonical file drift ایجاد شده باشد.

Consistency Review را وقتی درخواست کن که:

- چند Pack در یک Phase اجرا شده‌اند
- چند decision هنگام اجرا ایجاد شده است
- Remediationها accepted behavior را تغییر داده‌اند
- Pack instructionها ممکن است دیگر با Canonical files هماهنگ نباشند
- Run Reportها ممکن است با implementation واقعی هماهنگ نباشند
- Phase Canonical و Release Canonical ممکن است conflict داشته باشند
- قبل از Phase Final Review در یک Phase پیچیده

نمونه prompt پیشنهادی اپراتور:

```text
Create a Consistency Review for this Phase or scope.

Check consistency between:

- Packs
- Run Reports
- Decisions
- Canonical files
- Guides
- Change Requests
- Remediations
- current source code
```

---

## 16. پایان Phase

در پایان هر Phase، Phase Final Review درخواست کن.

Phase Final Review باید این موارد را بررسی کند:

- همه Packهای لازم برای Phase
- Run Reports
- tests
- open Decisions
- open Change Requests
- open Remediations
- دقت Phase Canonical
- آیا Phase Canonical باید final شود
- آیا تصمیمی از Phase باید به Release Canonical promote شود
- آیا Phase آماده بسته‌شدن است

بعد از هر Phase، Release Canonical را review کن.

Release Canonical را فقط وقتی update کن که یک decision واقعاً release-wide شده باشد.

نمونه prompt پیشنهادی اپراتور:

```text
Create the Phase Final Review.

Check whether:

- all required Packs are accepted
- all Run Reports are complete
- tests are complete
- open decisions are resolved or tracked
- open Change Requests are resolved or tracked
- open Remediations are resolved or tracked
- Phase Canonical reflects accepted Phase truth
- any Phase decision must be promoted to Release Canonical
- the Phase can be closed
```

---

## 17. پایان Release

در پایان هر Release، Release Final Review درخواست کن.

Release Final Review باید این موارد را بررسی کند:

- همه Phase Final Reviewها
- accepted Phase Canonical files
- دقت Release Canonical
- open Decisions
- open Change Requests
- open Remediations
- آمادگی Release
- آیا تصمیمی از Release باید به Global Canonical promote شود
- آیا پروژه می‌تواند وارد Release بعدی شود

بعد از هر Release، Global Canonical را review کن.

Global Canonical را فقط وقتی update کن که یک decision واقعاً project-wide شده باشد.

نمونه prompt پیشنهادی اپراتور:

```text
Create the Release Final Review.

Check whether:

- all Phases are accepted
- Phase Final Reviews are complete
- Release Canonical reflects accepted Release truth
- open decisions are resolved or tracked
- open Change Requests are resolved or tracked
- open Remediations are resolved or tracked
- any Release decision must be promoted to Global Canonical
- the Release can be closed
```

---

## 18. Source Update Review

وقتی تغییر مهمی در source ممکن است روی implementation assumptions یا accepted decisions اثر بگذارد، Source Update Review درخواست کن.

نمونه‌ها:

- framework update
- project/platform update
- dependency update
- dependency update
- provider documentation update
- source documentation update
- generated analysis update
- graph/onboarding update

نمونه prompt پیشنهادی اپراتور:

```text
Create a Source Update Review.

Check whether the source update affects:

- accepted implementation
- Canonical decisions
- Packs
- Guides
- tests
- source/reference validity
- Change Request needs
- Remediation needs
```

فقط به این دلیل که source documentation تغییر کرده، Canonical files را خودکار update نکن.

---

## 19. Documentation Maintenance

وقتی AI documentation تغییر می‌کند، اپراتور باید documentation maintenance را بررسی کند.

حداقل این موارد را بررسی کن:

- owner file
- related indexes
- template/rule sync
- Canonical/Decision/Guide sync
- Run/Review/Change/Remediation sync
- Required Follow-up Updates
- release/phase neutrality برای global files

نمونه prompt پیشنهادی اپراتور:

```text
Check documentation maintenance impact.

Confirm:

- owner file checked
- related indexes updated or listed as follow-up
- related templates checked
- related rules checked
- related Canonical/Decision/Guide files checked
- related Run/Review/Change/Remediation files checked
- no release/phase-specific content leaked into global files
```

---

## 20. رفتن به Pack بعدی

فقط وقتی به Pack بعدی برو که:

- Pack فعلی accepted شده یا عمداً blocked/superseded شده باشد
- اگر Run Report لازم است، کامل شده باشد
- اگر کار accepted است، commit کامل شده باشد
- follow-upهای حل‌نشده track شده باشند
- هیچ unsafe partial change باقی نمانده باشد
- وضعیت branch فعلی روشن باشد
- Pack بعدی هنوز با Canonical files و implementation state فعلی معتبر باشد

نمونه prompt پیشنهادی اپراتور:

```text
Before moving to the next Pack, confirm:

- current Pack status
- Run Report status
- commit status
- unresolved follow-ups
- open decisions
- open changes/remediations
- whether the next Pack still needs normalization
```
