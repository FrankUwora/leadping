=== LeadPing – Instant WhatsApp Lead Notifications ===
Contributors: frankuwora
Tags: whatsapp, leads, contact form, notifications, crm
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instant WhatsApp alerts to the business owner and auto-reply to the lead the moment a contact form is submitted. Never lose a lead again.

== Description ==

Most small businesses lose leads not because of a bad product or a bad website — but because they respond too slowly. Someone fills out a contact form, the business sees it 4 hours later, and by then the lead has already called someone else.

LeadPing fixes that.*

The moment someone submits a contact form on your WordPress site, LeadPing fires two instant WhatsApp messages:

1. The business owner gets an alert with the lead's name, phone number, email, message, and the page they came from — straight to their WhatsApp.
2. The lead gets an automatic reply letting them know their message was received and someone will be in touch shortly.

Everything is also logged inside a clean dashboard in your WordPress admin so you never lose track of a single enquiry.

= Who is this for? =

LeadPing is built for any service business where speed of response wins the job:

* Plumbers, electricians, and trades businesses
* Hair salons, barbershops, and beauty studios
* Real estate agents and property managers
* Lawyers, consultants, and coaches
* Clinics, dentists, and healthcare providers
* Any small business running a WordPress website

= Key Features =

* ⚡ Instant WhatsApp notification to the business owner on every form submission
* 🤖 Automatic WhatsApp reply sent to the lead within seconds
* 📋 Full leads dashboard with name, email, phone, message, source page, and notification status
* 📊 Stats panel showing total leads, today's leads, and this month's leads
* 🔌 Works out of the box with Contact Form 7, WPForms, Gravity Forms, and Elementor Forms
* ✏️ Fully customizable message templates with dynamic variables
* 🔐 Supports both 360dialog and Twilio as WhatsApp API providers
* 🛠️ Developer-friendly with a custom action hook after each lead is processed

= How It Works =

1. A visitor fills out a contact form on your website
2. LeadPing catches the submission instantly
3. A WhatsApp alert is sent to the business owner with the lead's full details
4. An automatic WhatsApp reply is sent to the lead confirming their message was received
5. The lead is logged in the dashboard with full notification status

= Supported Form Plugins =

* Contact Form 7
* WPForms
* Gravity Forms
* Elementor Forms (Pro)

= WhatsApp API Providers =

LeadPing connects to WhatsApp through the official WhatsApp Business API. You will need an account with one of these providers:

* **Twilio** – Recommended for getting started. Offers a free sandbox for testing with no Meta approval required.
* **360dialog** – Recommended for production and high volume. Requires Meta Business verification.

== Installation ==

1. Download the LeadPing plugin zip file
2. Go to your WordPress Admin and navigate to **Plugins > Add New > Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Click **Activate Plugin**
5. Navigate to **LeadPing > Settings** in your WordPress admin menu
6. Select your WhatsApp provider (Twilio or 360dialog)
7. Enter your API credentials and business WhatsApp number
8. Enter the owner phone number that should receive lead alerts
9. Save your settings and you are live

== Frequently Asked Questions ==

= Do I need a WhatsApp Business account? =

Yes. LeadPing connects to WhatsApp through the official WhatsApp Business API via Twilio or 360dialog. You will need an account with one of these providers to send messages. Twilio offers a free sandbox for testing which you can set up in minutes.

= Which contact form plugins does LeadPing support? =

LeadPing works out of the box with Contact Form 7, WPForms, Gravity Forms, and Elementor Forms Pro. It hooks into each plugin's native submission event so no extra configuration is needed on your forms.

= Does the lead need WhatsApp to receive the auto-reply? =

Yes. The auto-reply is sent via WhatsApp so the lead needs to have WhatsApp installed on the phone number they provide in the form. If they don't have WhatsApp, the owner notification will still be sent successfully.

= Can I customize the WhatsApp messages? =

Yes. You can fully customize both the owner notification message and the lead auto-reply message from the LeadPing settings page. Use the available variables to insert dynamic data like the lead's name, phone number, email, message, and more.

= What variables can I use in message templates? =

Owner notification template: {business} {name} {email} {phone} {message} {page} {time}
Lead auto-reply template: {business} {first_name}

= Is LeadPing free? =

Yes, LeadPing is completely free. You only pay for the WhatsApp API usage through your chosen provider (Twilio or 360dialog). Twilio's free trial includes $15 in credit which is enough to test and demo the plugin.

= Will it work with my existing forms? =

As long as you are using Contact Form 7, WPForms, Gravity Forms, or Elementor Forms, yes. You do not need to change or rebuild your existing forms. Just install LeadPing, configure your API settings, and it starts working automatically.

= Is my lead data stored securely? =

Yes. All lead data is stored in your own WordPress database. LeadPing does not send your lead data to any external server other than your chosen WhatsApp API provider for the purpose of sending the notification messages.

== Screenshots ==

1. LeadPing dashboard showing lead stats and full leads table
2. Settings page for WhatsApp API configuration and message templates
3. Example WhatsApp notification received by the business owner
4. Example auto-reply received by the lead on WhatsApp

== Changelog ==

= 1.0.0 =
* Initial release
* WhatsApp notifications via Twilio and 360dialog
* Support for Contact Form 7, WPForms, Gravity Forms, and Elementor Forms
* Leads dashboard with stats
* Customizable message templates for owner and lead

== Upgrade Notice ==

= 1.0.0 =
Initial release of LeadPing. Install and connect your WhatsApp API to get started.
