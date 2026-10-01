# Stripe & Paymob Learning Roadmap

خطة عملية لتعلّم الدفع الإلكتروني باستخدام Laravel، ثم تطبيق نفس المفاهيم مع Stripe وPaymob.

المدة المقترحة: **5 أسابيع** بمعدل **ساعة ونصف إلى ساعتين يوميًا**.

> الترتيب المقترح: أساسيات الدفع أولًا، ثم Stripe، ثم Paymob، ثم بناء طبقة موحدة تدعم الاثنين.

---

## النتيجة النهائية

بنهاية الخطة ستكون قادرًا على:

- فهم دورة حياة عملية الدفع.
- ربط Laravel مع Stripe Checkout.
- ربط Laravel مع Paymob Unified Checkout.
- استقبال Webhooks وCallbacks بصورة آمنة.
- منع تكرار الدفع أو تنفيذ الطلب أكثر من مرة.
- تنفيذ Refund كامل أو جزئي.
- إضافة Payment Provider جديد بدون تغيير منطق الطلبات.

---

## المرحلة 0: المتطلبات الأساسية

المدة المقترحة: **3 أيام**.

### HTTP وAPIs

- [ ] فهم REST APIs بصورة عامة.
- [ ] معرفة الفرق بين `GET` و`POST` و`PUT` و`DELETE`.
- [ ] إرسال واستقبال JSON.
- [ ] فهم HTTP status codes الأساسية.
- [ ] فهم API authentication باستخدام المفاتيح.
- [ ] تجربة API باستخدام Postman أو Bruno.

### Laravel

- [ ] Routes وControllers.
- [ ] Form Request Validation.
- [ ] Models وMigrations والعلاقات.
- [ ] Service Classes وDependency Injection.
- [ ] Laravel HTTP Client.
- [ ] Database Transactions.
- [ ] Queues وJobs.
- [ ] Logging.
- [ ] Feature Tests.
- [ ] استخدام `.env` وعدم وضع المفاتيح السرية داخل Git.

### تطبيق المرحلة

- [ ] إرسال request إلى API تجريبي باستخدام Laravel HTTP Client.
- [ ] التعامل مع success response وerror response.
- [ ] تسجيل الخطأ في logs بدون تسجيل أي أسرار.

---

## الأسبوع الأول: أساسيات الدفع الإلكتروني

هذه المفاهيم مشتركة بين Stripe وPaymob ومعظم مزودي الدفع.

### دورة الدفع

```text
إنشاء Order
    ↓
إنشاء Payment Attempt
    ↓
تحويل المستخدم إلى صفحة الدفع
    ↓
تنفيذ الدفع والمصادقة
    ↓
Webhook من مزود الدفع
    ↓
التحقق من التوقيع
    ↓
تحديث Payment
    ↓
تأكيد Order
```

### المصطلحات الأساسية

- [ ] `Order`: الطلب داخل المتجر.
- [ ] `Payment Attempt`: محاولة دفع الطلب.
- [ ] `Transaction`: العملية عند مزود الدفع.
- [ ] `Authorization`: حجز المبلغ.
- [ ] `Capture`: تحصيل المبلغ المحجوز.
- [ ] `Void`: إلغاء الحجز قبل التحصيل.
- [ ] `Refund`: استرجاع مبلغ تم تحصيله.
- [ ] `Partial Refund`: استرجاع جزء من المبلغ.
- [ ] `Settlement`: تسوية العملية.
- [ ] `Payout`: تحويل الأموال إلى حساب التاجر.
- [ ] `Dispute/Chargeback`: اعتراض العميل على العملية.
- [ ] `3D Secure`: التحقق الإضافي من حامل البطاقة.
- [ ] `Webhook`: إشعار Server-to-Server من مزود الدفع.

### قواعد مهمة

- [ ] السيرفر هو الذي يحسب السعر النهائي، وليس الـ frontend.
- [ ] لا يتم تخزين بيانات البطاقة داخل التطبيق.
- [ ] لا يتم تأكيد الطلب اعتمادًا على صفحة النجاح.
- [ ] يتم تأكيد الطلب من Webhook موثوق بعد التحقق من توقيعه.
- [ ] يجب أن تتحمل معالجة Webhook وصول الحدث أكثر من مرة.
- [ ] يجب مقارنة مبلغ العملية وعملتها بالطلب المحلي.
- [ ] الطلب الواحد يمكن أن يحتوي على أكثر من محاولة دفع.

### تصميم قاعدة البيانات

```text
orders
- id
- number
- customer_id
- total_amount
- currency
- status

payments
- id
- order_id
- provider
- provider_payment_id
- amount
- currency
- status
- paid_at
- failure_code
- failure_message
- metadata

payment_events
- id
- payment_id
- provider
- provider_event_id
- event_type
- payload
- processed_at
```

يفضل تخزين المبالغ كأرقام صحيحة بأصغر وحدة للعملة:

```php
// 100.50 EGP
$amount = 10050;
```

### حالات الدفع الداخلية

```text
pending
requires_action
authorized
paid
failed
cancelled
partially_refunded
refunded
```

### تطبيق الأسبوع

- [ ] إنشاء Order محلي.
- [ ] إنشاء Payment Attempt بدون Provider حقيقي.
- [ ] تطبيق الانتقال بين حالات الدفع.
- [ ] منع دفع Order مدفوع بالفعل.
- [ ] منع تنفيذ نفس Payment Event مرتين.

---

## الأسبوع الثاني: Stripe Checkout

ابدأ بـ Stripe Checkout فقط، ولا تبدأ بـ Payment Element الآن.

### إعداد Stripe

- [ ] إنشاء حساب وتجربة Test Mode.
- [ ] فهم الفرق بين Test Mode وLive Mode.
- [ ] فهم `Publishable Key`.
- [ ] فهم `Secret Key`.
- [ ] فهم `Webhook Secret`.
- [ ] تخزين المفاتيح في `.env`.
- [ ] تثبيت Stripe PHP SDK.

```bash
composer require stripe/stripe-php
```

### Checkout Session

- [ ] إنشاء Checkout Session من السيرفر.
- [ ] إرسال `line_items`.
- [ ] تحديد `mode`.
- [ ] إضافة `success_url` و`cancel_url`.
- [ ] إضافة `customer_email` عند الحاجة.
- [ ] استخدام `metadata` لربط العملية بالطلب.
- [ ] حفظ Checkout Session ID داخل Payment Attempt.
- [ ] تحويل العميل إلى صفحة Stripe المستضافة.

```text
POST /orders/{order}/pay
        ↓
Create Stripe Checkout Session
        ↓
Save Session ID
        ↓
Redirect to Stripe Checkout
```

### Stripe Webhooks

- [ ] إنشاء Stripe webhook endpoint.
- [ ] قراءة raw request body.
- [ ] التحقق باستخدام Webhook Secret.
- [ ] استخدام Stripe CLI للاختبار المحلي.
- [ ] حفظ Event ID ومنع تكرار معالجته.
- [ ] إعادة response سريع ثم تنفيذ العمل الثقيل في Queue.

الأحداث الأساسية:

```text
checkout.session.completed
checkout.session.async_payment_succeeded
checkout.session.async_payment_failed
payment_intent.payment_failed
```

> صفحة `success` تعرض النتيجة فقط، لكن الـ webhook هو الذي يؤكد الدفع والطلب.

### الأخطاء وIdempotency

- [ ] التعامل مع card errors.
- [ ] التعامل مع validation errors.
- [ ] التعامل مع network errors.
- [ ] التعامل مع rate limits.
- [ ] تطبيق retries بحذر.
- [ ] استخدام Idempotency Key عند إنشاء الدفع.
- [ ] تسجيل Stripe Request ID لتسهيل تتبع الأخطاء.

مثال لمفتاح Idempotency:

```text
order-123-payment-attempt-1
```

### تطبيق الأسبوع

- [ ] إنشاء Order.
- [ ] إنشاء Stripe Checkout Session.
- [ ] تجربة الدفع الناجح.
- [ ] تجربة الدفع الفاشل.
- [ ] تجربة إلغاء العميل للعملية.
- [ ] تحديث Order من Webhook فقط.
- [ ] اختبار وصول نفس Webhook مرتين.

### مراجع Stripe

- [Stripe Checkout Quickstart](https://docs.stripe.com/payments/checkout/quickstarts)
- [Stripe Webhooks](https://docs.stripe.com/webhooks)
- [Stripe Idempotent Requests](https://docs.stripe.com/api/idempotent_requests)
- [Stripe Testing](https://docs.stripe.com/testing)

---

## الأسبوع الثالث: Stripe PaymentIntent

ابدأ هذه المرحلة بعد إكمال Stripe Checkout.

### PaymentIntent Lifecycle

- [ ] فهم `requires_payment_method`.
- [ ] فهم `requires_confirmation`.
- [ ] فهم `requires_action`.
- [ ] فهم `processing`.
- [ ] فهم `requires_capture`.
- [ ] فهم `succeeded`.
- [ ] فهم `canceled`.

### علاقة كائنات Stripe

- [ ] `Customer`.
- [ ] `PaymentMethod`.
- [ ] `PaymentIntent`.
- [ ] `Charge`.
- [ ] `Refund`.

### Payment Element

- [ ] إنشاء PaymentIntent من السيرفر.
- [ ] إرسال `client_secret` فقط إلى الواجهة.
- [ ] عرض Stripe Payment Element.
- [ ] تنفيذ confirmation.
- [ ] التعامل مع 3D Secure.
- [ ] عدم إرسال Secret Key إلى الواجهة.

### إدارة المدفوعات

- [ ] الاستعلام عن PaymentIntent.
- [ ] Full refund.
- [ ] Partial refund.
- [ ] Manual capture.
- [ ] إلغاء PaymentIntent.
- [ ] منع تنفيذ refund بقيمة أكبر من المبلغ المتاح.

### حفظ وسيلة الدفع — اختياري

- [ ] فهم SetupIntent.
- [ ] ربط PaymentMethod بـ Customer.
- [ ] فهم On-session payments.
- [ ] فهم Off-session payments.
- [ ] التعامل مع المصادقة المطلوبة لاحقًا.

### موضوعات تؤجل حاليًا

- [ ] Stripe Billing وSubscriptions.
- [ ] Stripe Connect.
- [ ] Stripe Tax.
- [ ] Invoicing.
- [ ] Stripe Terminal.

ادرس هذه الأجزاء فقط عندما يحتاجها المشروع.

---

## الأسبوع الرابع: Paymob

استخدم **Intention API + Unified Checkout**. تجنب البدء من Tutorials قديمة تعتمد على تدفقات Paymob القديمة.

### إعداد Paymob

- [ ] إنشاء حساب واستخدام بيئة الاختبار.
- [ ] معرفة Secret Key.
- [ ] معرفة Public Key عند الحاجة.
- [ ] معرفة Integration ID.
- [ ] معرفة HMAC Secret.
- [ ] فهم الفرق بين Test وLive credentials.
- [ ] معرفة طرق الدفع المفعلة على الحساب.

### Create Intention

- [ ] إرسال `amount`.
- [ ] إرسال `currency`.
- [ ] إرسال `payment_methods`.
- [ ] إرسال `items`.
- [ ] إرسال `billing_data`.
- [ ] إنشاء `special_reference` فريد.
- [ ] إضافة `notification_url`.
- [ ] إضافة `redirection_url`.

احفظ البيانات المهمة في Payment Attempt:

```text
intention_id
order_id
client_secret
special_reference
```

استخدم `special_reference` أو معرّفًا داخليًا موثوقًا لربط عملية Paymob بمحاولة الدفع.

### Unified Checkout

- [ ] إنشاء Intention.
- [ ] استلام `client_secret`.
- [ ] تكوين رابط Unified Checkout.
- [ ] تحويل العميل إلى Checkout.
- [ ] اختبار النجاح والفشل والإلغاء.

```text
Create Paymob Intention
        ↓
Receive Client Secret
        ↓
Redirect to Unified Checkout
        ↓
Customer Completes Payment
```

ابدأ بطريقة Redirect، وأجّل Pixel أو Embedded Checkout.

### Paymob Callbacks

#### Transaction Processed Callback

- [ ] يستقبل `POST` من Paymob.
- [ ] يستخدم لتحديث Payment وOrder.
- [ ] التحقق من HMAC قبل معالجة البيانات.
- [ ] تخزين Transaction ID لمنع التكرار.

#### Transaction Response Callback

- [ ] يستقبل `GET` بعد رجوع Browser العميل.
- [ ] يستخدم لعرض نتيجة مناسبة للمستخدم.
- [ ] لا يستخدم وحده لتأكيد الدفع.

الحقول المهمة:

```text
id
success
pending
amount_cents
currency
order.id
is_refunded
refunded_amount_cents
is_voided
is_captured
```

### HMAC Verification

- [ ] قراءة تعليمات HMAC لنوع Callback المستخدم.
- [ ] ترتيب الحقول بالترتيب المحدد في توثيق Paymob.
- [ ] جمع قيم الحقول بالشكل المطلوب.
- [ ] حساب HMAC باستخدام السر والخوارزمية المحددين.
- [ ] استخدام `hash_equals` للمقارنة الآمنة.
- [ ] رفض أي Callback يحمل توقيعًا خاطئًا.

> لا تفترض أن كل أنواع Callbacks تستخدم نفس الحقول أو نفس طريقة التجميع.

### إدارة عمليات Paymob

- [ ] Transaction inquiry.
- [ ] Full refund.
- [ ] Partial refund.
- [ ] Void.
- [ ] Capture إذا كان مفعّلًا.
- [ ] فهم settlement ومتابعة العمليات من Dashboard.

### تطبيق الأسبوع

- [ ] إنشاء Paymob Intention.
- [ ] التحويل إلى Unified Checkout.
- [ ] استقبال Processed Callback.
- [ ] التحقق من HMAC.
- [ ] استقبال Response Callback لعرض النتيجة.
- [ ] معالجة النجاح والفشل.
- [ ] اختبار وصول Callback أكثر من مرة.
- [ ] تنفيذ Refund.

### مراجع Paymob

- [Paymob Create Intention](https://developers.paymob.com/paymob-docs/intention-apis/create-intention)
- [Paymob Transaction Callbacks](https://developers.paymob.com/paymob-docs/developers/webhook-callbacks-and-hmac/transaction-callbacks)
- [Paymob API Integration Paths](https://developers.paymob.com/paymob-docs/integration-paths/apis)

---

## الأسبوع الخامس: دعم Stripe وPaymob معًا

الهدف هو فصل منطق المتجر عن تفاصيل مزود الدفع.

### Contract موحد

```php
interface PaymentGateway
{
    public function createPayment(
        Order $order,
        Payment $payment
    ): PaymentRedirect;

    public function handleWebhook(
        string $payload,
        array $headers
    ): PaymentEvent;

    public function refund(
        Payment $payment,
        int $amount
    ): RefundResult;
}
```

التطبيقات:

```text
PaymentGateway
├── StripeGateway
└── PaymobGateway
```

### نتيجة موحدة

```php
final class PaymentEvent
{
    public function __construct(
        public string $providerEventId,
        public string $providerPaymentId,
        public string $status,
        public int $amount,
        public string $currency,
    ) {}
}
```

يجب ألا يعرف Order Service تفاصيل مثل:

- Checkout Session.
- PaymentIntent.
- Paymob Intention.
- Paymob HMAC fields.

### Routes مقترحة

```text
POST /payments/stripe/create
POST /webhooks/stripe

POST /payments/paymob/create
POST /webhooks/paymob
GET  /payments/paymob/response
```

### اختبارات مطلوبة لكل Provider

- [ ] نجاح الدفع.
- [ ] فشل الدفع.
- [ ] إلغاء العميل.
- [ ] وصول Webhook مرتين.
- [ ] وصول الأحداث بترتيب مختلف.
- [ ] مبلغ غير مطابق للطلب.
- [ ] عملة غير مطابقة.
- [ ] توقيع غير صحيح.
- [ ] Timeout أثناء إنشاء الدفع.
- [ ] Full refund.
- [ ] Partial refund.
- [ ] محاولة دفع Order مدفوع بالفعل.
- [ ] وصول Webhook لعملية غير موجودة.

---

## المشروع النهائي

أنشئ Checkout يسمح باختيار مزود الدفع:

```text
اختر وسيلة الدفع:

○ Stripe
○ Paymob
```

### متطلبات المشروع

- [ ] Orders منفصلة عن Payments.
- [ ] دعم أكثر من Payment Attempt للطلب.
- [ ] Stripe Checkout.
- [ ] Paymob Unified Checkout.
- [ ] Webhooks وCallbacks مؤمنة.
- [ ] منع معالجة الحدث أكثر من مرة.
- [ ] مطابقة المبلغ والعملة.
- [ ] Full وPartial Refunds.
- [ ] صفحة Admin لعرض العمليات.
- [ ] Logs بدون مفاتيح أو بيانات حساسة.
- [ ] Queue لمعالجة المهام الثقيلة.
- [ ] Feature Tests للـ Webhooks والCallbacks.

---

## ترتيب الأولويات

### يجب إتقانه

- [ ] Payment lifecycle.
- [ ] Hosted Checkout redirects.
- [ ] Webhooks وCallbacks.
- [ ] Signature وHMAC verification.
- [ ] Idempotency.
- [ ] مطابقة المبلغ والعملة.
- [ ] Refunds.
- [ ] Error handling.
- [ ] فصل Orders عن Payment Attempts.

### يكفي فهم فكرته في البداية

- [ ] Authorization وCapture.
- [ ] Settlement وPayouts.
- [ ] Disputes.
- [ ] Saved cards.

### يؤجل حتى يحتاجه المشروع

- [ ] Subscriptions.
- [ ] Marketplace وStripe Connect.
- [ ] Split payments.
- [ ] Advanced fraud rules.
- [ ] Custom embedded card forms.

---

## معيار إتمام الخطة

تعتبر الخطة مكتملة عندما تستطيع:

1. إنشاء Order ومحاولة دفع من Laravel.
2. إرسال العميل إلى Stripe أو Paymob.
3. استقبال وتوثيق Webhook أو Callback.
4. تحديث الطلب مرة واحدة فقط مهما تكرر الحدث.
5. التعامل مع النجاح والفشل والإلغاء والاسترجاع.
6. إضافة Provider ثالث عن طريق Adapter جديد دون تعديل منطق الطلبات.

