<?php

        $pattern = '/("[^"]*(?:""[^"]*)*"|[^,]*),/';
        $test = "47201,\"90101\",\"9,010154\",\"ｵｷﾅﾜｹﾝ\",\"ﾅﾊｼ\",\"ｱｶﾐﾈ\",\"沖縄県\",\"那\"\"覇市\",\"赤嶺\",0";
        $test .= ',';
        $matches = array();
        
        preg_match_all($pattern, $test, $matches);
        print_r($matches);
        
        
        print_r($matches);
        

        
        
        
        ?>