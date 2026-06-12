<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class LeadPing_Dashboard {

    public function __construct() {
        add_action( 'admin_menu',  [ $this, 'register_menu' ] );
        add_action( 'admin_init',  [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    // ------------------------------------------------------------------ //
    //  Menu
    // ------------------------------------------------------------------ //

    public function register_menu(): void {
        add_menu_page(
            'LeadPing',
            'LeadPing',
            'manage_options',
            'leadping',
            [ $this, 'render_dashboard' ],
            'dashicons-bell',
            30
        );

        add_submenu_page(
            'leadping',
            'All Leads',
            'All Leads',
            'manage_options',
            'leadping',
            [ $this, 'render_dashboard' ]
        );

        add_submenu_page(
            'leadping',
            'Settings',
            'Settings',
            'manage_options',
            'leadping-settings',
            [ $this, 'render_settings' ]
        );
    }

    // ------------------------------------------------------------------ //
    //  Settings
    // ------------------------------------------------------------------ //

    public function register_settings(): void {
        $fields = [
            'leadping_provider',
            'leadping_api_key',
            'leadping_from_number',
            'leadping_owner_phone',
            'leadping_twilio_sid',
            'leadping_owner_template',
            'leadping_lead_template',
        ];
        foreach ( $fields as $field ) {
            register_setting( 'leadping_settings', $field, [ 'sanitize_callback' => 'sanitize_text_field' ] );
        }
        // Templates need textarea sanitization
        register_setting( 'leadping_settings', 'leadping_owner_template', [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
        register_setting( 'leadping_settings', 'leadping_lead_template',  [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
    }

    // ------------------------------------------------------------------ //
    //  Assets
    // ------------------------------------------------------------------ //

    public function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, 'leadping' ) === false ) return;
        wp_enqueue_style( 'leadping-admin', LEADPING_URL . 'assets/admin.css', [], LEADPING_VERSION );
    }

    // ------------------------------------------------------------------ //
    //  Dashboard page
    // ------------------------------------------------------------------ //

    public function render_dashboard(): void {
        $total        = LeadPing_DB::count();
        $today        = LeadPing_DB::count_today();
        $month        = LeadPing_DB::count_this_month();
        $per_page     = 20;
        $current_page = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $offset       = ( $current_page - 1 ) * $per_page;
        $leads        = LeadPing_DB::get_leads( $per_page, $offset );
        $total_pages  = ceil( $total / $per_page );
        ?>
        <div class="wrap lp-wrap">

            <h1 class="lp-title">⚡ LeadPing <span>Dashboard</span></h1>

            <!-- Stats -->
            <div class="lp-stats">
                <div class="lp-stat-card">
                    <span class="lp-stat-number"><?php echo esc_html( $total ); ?></span>
                    <span class="lp-stat-label">Total Leads</span>
                </div>
                <div class="lp-stat-card lp-stat-green">
                    <span class="lp-stat-number"><?php echo esc_html( $today ); ?></span>
                    <span class="lp-stat-label">Today</span>
                </div>
                <div class="lp-stat-card lp-stat-blue">
                    <span class="lp-stat-number"><?php echo esc_html( $month ); ?></span>
                    <span class="lp-stat-label">This Month</span>
                </div>
            </div>

            <!-- Leads table -->
            <?php if ( empty( $leads ) ) : ?>
                <div class="lp-empty">
                    <p>No leads yet. Once your forms start getting submissions, they'll appear here.</p>
                </div>
            <?php else : ?>
            <table class="lp-table widefat">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Source</th>
                        <th>Owner Notified</th>
                        <th>Lead Notified</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $leads as $lead ) : ?>
                    <tr>
                        <td><?php echo esc_html( $lead->id ); ?></td>
                        <td><?php echo esc_html( $lead->name ); ?></td>
                        <td><?php echo esc_html( $lead->email ); ?></td>
                        <td><?php echo esc_html( $lead->phone ); ?></td>
                        <td class="lp-msg"><?php echo esc_html( wp_trim_words( $lead->message, 10 ) ); ?></td>
                        <td><?php echo esc_html( $lead->source_form ); ?></td>
                        <td><?php echo $lead->owner_notified ? '<span class="lp-badge lp-ok">✓ Sent</span>' : '<span class="lp-badge lp-fail">✗ Failed</span>'; ?></td>
                        <td><?php echo $lead->lead_notified  ? '<span class="lp-badge lp-ok">✓ Sent</span>' : '<span class="lp-badge lp-fail">✗ Failed</span>'; ?></td>
                        <td><?php echo esc_html( date_i18n( 'M j, Y H:i', strtotime( $lead->created_at ) ) ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ( $total_pages > 1 ) : ?>
            <div class="lp-pagination">
                <?php
                echo paginate_links([
                    'base'    => add_query_arg( 'paged', '%#%' ),
                    'format'  => '',
                    'current' => $current_page,
                    'total'   => $total_pages,
                ]);
                ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

        </div>
        <?php
    }

    // ------------------------------------------------------------------ //
    //  Settings page
    // ------------------------------------------------------------------ //

    public function render_settings(): void {
        $provider = get_option( 'leadping_provider', '360dialog' );
        ?>
        <div class="wrap lp-wrap">
            <h1 class="lp-title">⚡ LeadPing <span>Settings</span></h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'leadping_settings' ); ?>

                <div class="lp-settings-grid">

                    <!-- API Config -->
                    <div class="lp-card">
                        <h2>WhatsApp API</h2>

                        <label>Provider</label>
                        <select name="leadping_provider">
                            <option value="360dialog" <?php selected( $provider, '360dialog' ); ?>>360dialog</option>
                            <option value="twilio"    <?php selected( $provider, 'twilio' ); ?>>Twilio</option>
                        </select>

                        <label>API Key / Auth Token</label>
                        <input type="password" name="leadping_api_key"
                               value="<?php echo esc_attr( get_option( 'leadping_api_key', '' ) ); ?>"
                               placeholder="Your API key" class="regular-text" />

                        <label>From Number (E.164 e.g. +1415...)</label>
                        <input type="text" name="leadping_from_number"
                               value="<?php echo esc_attr( get_option( 'leadping_from_number', '' ) ); ?>"
                               placeholder="+14155238886" class="regular-text" />

                        <div id="twilio-extra" style="<?php echo $provider !== 'twilio' ? 'display:none' : ''; ?>">
                            <label>Twilio Account SID</label>
                            <input type="text" name="leadping_twilio_sid"
                                   value="<?php echo esc_attr( get_option( 'leadping_twilio_sid', '' ) ); ?>"
                                   placeholder="ACxxxxxxxxxxxxxxxx" class="regular-text" />
                        </div>
                    </div>

                    <!-- Notification config -->
                    <div class="lp-card">
                        <h2>Notifications</h2>

                        <label>Business Owner WhatsApp Number (E.164)</label>
                        <input type="text" name="leadping_owner_phone"
                               value="<?php echo esc_attr( get_option( 'leadping_owner_phone', '' ) ); ?>"
                               placeholder="+2348012345678" class="regular-text" />

                        <p class="description">
                            This is the number that receives a WhatsApp alert every time a new lead submits a form.
                        </p>
                    </div>

                    <!-- Message templates -->
                    <div class="lp-card lp-card-full">
                        <h2>Message Templates</h2>
                        <p class="description">Leave blank to use the default messages. Available variables are shown below each field.</p>

                        <label>Owner Notification Message</label>
                        <textarea name="leadping_owner_template" rows="6" class="large-text"
                            placeholder="Leave blank to use default"><?php echo esc_textarea( get_option( 'leadping_owner_template', '' ) ); ?></textarea>
                        <p class="description">Variables: {business} {name} {email} {phone} {message} {page} {time}</p>

                        <label>Lead Auto-Reply Message</label>
                        <textarea name="leadping_lead_template" rows="6" class="large-text"
                            placeholder="Leave blank to use default"><?php echo esc_textarea( get_option( 'leadping_lead_template', '' ) ); ?></textarea>
                        <p class="description">Variables: {business} {first_name}</p>
                    </div>

                </div>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>

        <script>
        document.querySelector('[name="leadping_provider"]').addEventListener('change', function() {
            document.getElementById('twilio-extra').style.display = this.value === 'twilio' ? '' : 'none';
        });
        </script>
        <?php
    }
}
