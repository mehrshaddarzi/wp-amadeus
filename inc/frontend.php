<?php

namespace WP_AMADEUS;

class FrontEnd
{
    public function __construct()
    {
        add_shortcode('flight-offers', [$this, 'shortcode']);
    }

    public function shortcode($atts)
    {
        global $post;
     
        $pagelink = get_the_permalink($post->ID);
        $params = [
            'originLocationCode' => (isset($_GET['originLocationCode']) ? trim($_GET['originLocationCode']) : 'SYD'),
            'destinationLocationCode' => (isset($_GET['destinationLocationCode']) ? trim($_GET['destinationLocationCode']) : 'BKK'),
            'departureDate' => (isset($_GET['departureDate']) ? trim($_GET['departureDate']) : '2023-05-02'),
            'adults' => (isset($_GET['adults']) ? trim($_GET['adults']) : '1'),
        ];
        $query = null;
        if(!empty($_GET['originLocationCode']) and !empty($_GET['destinationLocationCode']) and !empty($_GET['departureDate']) and !empty($_GET['adults'])) {
            $query = WP_AMADEUS_REST::flight_offers($params);
        }
     
        ob_start();
        include \WP_Amadeus::$plugin_path . '/templates/flight-offers.php';
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

}

new FrontEnd();