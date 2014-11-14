<?php

namespace PHPemEventTable;

use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    /**
     * @return array
     */
    public function info()
    {
        $data = array(
            'name' => "PHPemEventTable",
            'description' => "Event table creator for PHPem",
            'author' => "Jack Bentley (Viva IT)",
            'link' => "http://www.vivait.co.uk",
            'version' => "1.0",
            'required_bolt_version' => "1.5",
            'highest_bolt_version' => "2.0",
            'type' => "Twig function",
            'first_releasedate' => "0000-00-00",
            'latest_releasedate' => "0000-00-00",
            'allow_in_user_content' => true,
        );

        return $data;
    }

    public function initialize()
    {
        $this->addTwigFunction('eventtable', 'createTable');
    }

    public function createTable($uevents)
    {
        if(!is_array($uevents))
            $uevents = array($uevents);

        $days = array('S', 'M', 'T', 'W', 'T', 'F', 'S');

        $thisDay = getdate();
        $fdom = mktime(0,0,0,$thisDay['mon'],1,$thisDay['year']);
        $firstDayOfMonth = getdate($fdom);
        $dayOfWeek = $firstDayOfMonth['wday']; // 0sun-7sat
        $numberDays = date('t',$fdom);

        $events = array();

        foreach($uevents as $item) {
            $item_time = strtotime($item->values['eventtime']);
            $item_time = getdate($item_time);

            $events[$item_time['mday']][] = $item;
        }

        $html = '<table class="calendar"><tbody><tr>';

        foreach($days as $day) {
            $html .= '<th scope="col">' . $day . '</th>';
        }

        $html .= '</tr><tr>';

        $currentDay = 1;

        for($i=0;$i<$dayOfWeek;$i++) {
            $html .= "<td></td>";
        }

        while($currentDay <= $numberDays) {

            $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);

            $date = $thisDay['year'] . "-" . $thisDay['month'] . "-" . $currentDayRel;

            $html .= '<td rel="' . $date . '" class="' . ($currentDay == $thisDay['mday'] ? 'today' : '') . '">' . $currentDayRel;

            if(isset($events[$currentDay]))
                foreach($events[$currentDay] as $item) {
                    $html .= '<a href="' . $item->link() . '">' . $item->values['title'] . '</a>';
                }

            $html .= '</td>';

            if($dayOfWeek == 6) {
                $html .= "</tr><tr>";
                $dayOfWeek = -1;
            }

            $currentDay++;
            $dayOfWeek++;
        }

        while($dayOfWeek<=6) {
            $html .= "<td></td>";
            $dayOfWeek++;
        }

        $html .= '</tr></table>';

        return new \Twig_Markup($html, 'UTF-8');
    }
}