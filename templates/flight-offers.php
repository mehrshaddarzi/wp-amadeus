<div class="flight-offers">
    <!-- Form -->
    <form method="GET" action="<?php echo $pagelink; ?>">
        <table>
            <tr>
                <td>originLocationCode</td>
                <td><input type="text" name="originLocationCode" value="<?php echo $params['originLocationCode']; ?>"></td>
            </tr>
            
            <tr>
                <td>destinationLocationCode</td>
                <td><input type="text" name="destinationLocationCode" value="<?php echo $params['destinationLocationCode']; ?>"></td>
            </tr>
            
            <tr>
                <td>departureDate</td>
                <td><input type="date" name="departureDate" value="<?php echo $params['departureDate']; ?>"></td>
            </tr>
            
            <tr>
                <td>adults</td>
                <td><input type="number" min="1" name="adults" value="<?php echo $params['adults']; ?>"></td>
            </tr>
            
            <tr>
                <td><input type="submit" value="Search Flights"></td>
            </tr>
        </table>
    </form>
    
    <!-- Error -->
    <?php
    if(is_array($query) and $query['status'] ===false) {
        ?>
        <div class="alert-error"><?php echo $query['message']; ?></div>   
        <?php
    }
    ?>
    
    <!-- Not Found -->
    <?php
    if(is_array($query) and $query['status'] ===true and isset($query['data']['data']) and empty($query['data']['data'])) {
        ?>
        <div class="alert-error">The search did not contain any results</div>   
        <?php
    }
    ?>
    
    <!-- List -->
    <?php
    if(is_array($query) and $query['status'] ===true and isset($query['data']['data']) and count($query['data']['data']) >0) {
        
        $i = 1;
        foreach($query['data']['data'] as $item) {
            ?>
            <div class="flight-offers-item">
               <p class="enum"><?php echo $i; ?>.</p>
               <div class="flight-offers-detail">
                   <table>
                        <tr>
                            <td>Source</td>
                            <td><?php echo $item['source']; ?></td>
                        </tr>
                        
                        <tr>
                            <td>Last Ticketing Date</td>
                            <td><?php echo $item['lastTicketingDate']; ?></td>
                        </tr>
                        
                        <tr>
                            <td style="width: 200px;">Number Of Bookable Seats</td>
                            <td><?php echo $item['numberOfBookableSeats']; ?></td>
                        </tr>
                        
                        <tr>
                            <td>Price</td>
                            <td><?php echo $item['price']['total']; ?> <?php echo $item['price']['currency'] ?></td>
                        </tr>
                        
                        <?php
                            if(isset($item['itineraries'][0])) {
                                ?>
                                
                                  <tr>
                                    <td style="vertical-align: top;">Itineraries</td>
                                    <td>
                                        <p>Duration: <span><?php echo $item['itineraries'][0]['duration']; ?></span></p>
                                        <p>Segments:</p>
                                        <p>
                                            <ol>
                                                <?php
                                                foreach($item['itineraries'][0]['segments'][0] as $k => $v) {
                                                    if(!in_array($k, ['departure', 'arrival'])) {
                                                        continue;
                                                    }
                                                    ?>
                                                    
                                                    <li>
                                                        <?php echo $k; ?>: iataCode <?php echo $v['iataCode'] ?>,
                                                        
                                                        <?php
                                                            if(isset($v['terminal'])) {
                                                                echo 'Terminal '.$v['terminal'];
                                                            }
                                                        ?>
                                                        At <?php echo $v['at'] ?></li>
                                                    <?php
                                                }
                                                ?>
                                            </ol>
                                        </p>
                                    </td>
                                </tr>
                                
                                <?php
                            }
                        ?>
  
                 </table>
               </div>
            </div>
            <?php
            $i++;
        }
    }
    ?>
    
    
    
</div>
<style>
    .alert-error {
        margin: 5%;
        background: #f7f7f7;
        border-radius: 5px;
        padding: 5%;
    }
    
    .flight-offers-item {
        background: #f9f9f9;
        padding: 5px 0 0 15px;
        margin-top: 35px;
        font-size: 13px;
    }
    
    .enum {
        color: #c7c7c7;
        font-size: 18px;
    }
</style>