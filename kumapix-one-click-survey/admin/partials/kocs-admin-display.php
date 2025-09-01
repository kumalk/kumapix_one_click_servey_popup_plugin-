<div class="wrap kocs-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="?page=kocs-survey&tab=settings" class="nav-tab <?php echo !isset($_GET['tab']) || $_GET['tab'] == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        <a href="?page=kocs-survey&tab=submissions" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'submissions' ? 'nav-tab-active' : ''; ?>">Submissions & Reports</a>
    </h2>

    <?php
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

    if ($active_tab == 'settings') {
    ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('kocs_option_group');
            do_settings_sections('kocs-survey');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Survey</th>
                    <td>
                        <label for="kocs_enabled">
                            <input type="checkbox" id="kocs_enabled" name="kocs_enabled" value="1" <?php checked(1, get_option('kocs_enabled'), true); ?> />
                            Enable the survey popup on the frontend.
                        </label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Survey Question</th>
                    <td><input type="text" name="kocs_question" value="<?php echo esc_attr(get_option('kocs_question')); ?>" class="regular-text" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Answers (One per line)</th>
                    <td><textarea name="kocs_answers" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('kocs_answers')); ?></textarea></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Popup Trigger</th>
                    <td>
                        <select name="kocs_trigger" id="kocs_trigger">
                            <option value="exit_intent" <?php selected(get_option('kocs_trigger'), 'exit_intent'); ?>>On Exit Intent</option>
                            <option value="timed" <?php selected(get_option('kocs_trigger'), 'timed'); ?>>After a set time</option>
                        </select>
                    </td>
                </tr>
                
                <tr valign="top" class="kocs-timed-delay">
                    <th scope="row">Time Delay (in seconds)</th>
                    <td><input type="number" name="kocs_trigger_delay" value="<?php echo esc_attr(get_option('kocs_trigger_delay', 5)); ?>" class="small-text" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Popup Background Color</th>
                    <td><input type="text" name="kocs_bg_color" value="<?php echo esc_attr(get_option('kocs_bg_color')); ?>" class="kocs-color-picker" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Popup Text Color</th>
                    <td><input type="text" name="kocs_text_color" value="<?php echo esc_attr(get_option('kocs_text_color')); ?>" class="kocs-color-picker" /></td>
                </tr>

                 <tr valign="top">
                    <th scope="row">Button Color</th>
                    <td><input type="text" name="kocs_btn_color" value="<?php echo esc_attr(get_option('kocs_btn_color')); ?>" class="kocs-color-picker" /></td>
                </tr>

                 <tr valign="top">
                    <th scope="row">Button Text Color</th>
                    <td><input type="text" name="kocs_btn_text_color" value="<?php echo esc_attr(get_option('kocs_btn_text_color')); ?>" class="kocs-color-picker" /></td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>

    <?php
    } else { // Submissions Tab
        global $wpdb;
        $table_name = $wpdb->prefix . 'kocs_submissions';
        $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        // Build Query
        $query = "SELECT * FROM {$table_name}";
        $conditions = [];
        if(!empty($start_date)) {
            $conditions[] = $wpdb->prepare("submission_time >= %s", $start_date . ' 00:00:00');
        }
        if(!empty($end_date)) {
            $conditions[] = $wpdb->prepare("submission_time <= %s", $end_date . ' 23:59:59');
        }
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY submission_time DESC";

        $submissions = $wpdb->get_results($query);

    ?>
    <div class="kocs-reports">
        <div class="report-summary">
            <h3>Overall Stats</h3>
            <p>Total Submissions: <strong><?php echo $total_submissions; ?></strong></p>
            <canvas id="kocs-chart" width="400" height="200"></canvas>
        </div>
        <div class="report-data">
            <h3>Submissions</h3>
            <form method="post" action="">
                <label for="start_date">From:</label>
                <input type="text" id="start_date" name="start_date" class="datepicker" value="<?php echo esc_attr($start_date); ?>">
                <label for="end_date">To:</label>
                <input type="text" id="end_date" name="end_date" class="datepicker" value="<?php echo esc_attr($end_date); ?>">
                <input type="submit" value="Filter" class="button">
            </form>
             <button id="kocs-export-csv" class="button button-primary">Download as CSV</button>
             <span class="spinner"></span>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($submissions) : ?>
                    <?php foreach ($submissions as $submission) : ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($submission->submission_time)); ?></td>
                            <td><?php echo esc_html($submission->question); ?></td>
                            <td><?php echo esc_html($submission->answer); ?></td>
                            <td><?php echo esc_html(trim($submission->city . ', ' . $submission->country, ', ')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No submissions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    }
    ?>
</div>
