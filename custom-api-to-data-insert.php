<?php
/**
 * Plugin Name: Custom API to Data Insert
 * Description: A plugin to insert data into a custom database table.
 * Version: 1.0
 * Author: Sakir
 */


 // Hook the function to the activation hook
register_activation_hook(__FILE__, 'mizv_create_custom_table');

function mizv_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fb_group_1_users';

    // SQL to create your table
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        user_full_name varchar(255) NOT NULL,
        user_link varchar(255) NOT NULL,
        qus_ans text NOT NULL,
        datatime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (ID)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}




add_action('rest_api_init', function () {
   register_rest_route('api', '/fb_group_1_users', array(
       'methods' => 'POST',
       'callback' => 'mizv_insert_fb_group_1_users_data',
       'permission_callback' => '__return_true'
   ));
});



function mizv_insert_fb_group_1_users_data(WP_REST_Request $request) {




   global $wpdb;
   $table_name = $wpdb->prefix . 'fb_group_1_users';
   $parameters = $request->get_json_params();
   
 //  error_log(print_r($parameters['qus_ans'], true));

   $user_full_name = sanitize_text_field($parameters['user_full_name']);
   $user_link = sanitize_text_field($parameters['user_link']);
   $qus_ans = json_encode($parameters['qus_ans']); // Serializes the array for storage
   $datatime = current_time('mysql', 1); // Get the current time in MySQL format

   $result = $wpdb->insert(
       $table_name,
       array(
           'user_full_name' => $user_full_name,
           'user_link' => $user_link,
           'qus_ans' => $qus_ans,
           'datatime' => $datatime
       ),
       array(
           '%s',
           '%s',
           '%s'
       )
   );

   if ($result) {
      $response_data = array(
          'status' => 'success',
          'message' => 'Saved successfully'
      );
      return new WP_REST_Response($response_data, 200);
  } else {
      $response_data = array(
          'status' => 'error',
          'message' => 'Failed to insert data'
      );
      return new WP_REST_Response($response_data, 500);
  }
}





function mizv_display_fb_group_1_users() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fb_group_1_users';
    
    // Fetch data from the custom table
//    $users_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    $users_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ID DESC", ARRAY_A);

    // Start output buffering to capture HTML output
    ob_start();

    // Check if we have any data returned
    if (!empty($users_data)) {
        echo '<table>';
        echo '<tr><th>ID</th><th>The Users</th><th>Questions & Answers</th><th>Date Time</th></tr>';

        // Loop through each user and display their data
        foreach ($users_data as $user) {
            // Decode the qus_ans JSON string
            $qus_ans = json_decode($user['qus_ans'], true);

            echo '<tr>';
            echo '<td>' . esc_html($user['ID']) . '</td>';
            echo '<td>' . esc_html($user['user_full_name']) . ' <a href="https://facebook.com' . esc_url($user['user_link']) . '">'. esc_html($user['user_link']) .'</a></td>';
            echo '<td>';
            if (!empty($qus_ans)) {
                // Loop through each question and answer
                foreach ($qus_ans as $qa) {
                    echo esc_html($qa['question']) . '<br>';
                    echo '<strong>' . esc_html($qa['answer']) . '</strong><br><br>';
                }
            }
            echo '</td>';
            echo '<td>' . esc_html($user['datatime']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p>No user data found.</p>';
    }

    // Get the buffered content into a variable
    $output = ob_get_clean();

    // Return the buffered content
    return $output;
}

// Register the shortcode with WordPress
add_shortcode('fb_group_1_users', 'mizv_display_fb_group_1_users');

