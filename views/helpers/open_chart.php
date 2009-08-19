<?php
    App::import( 'Vendor', 'flashchart/open-flash-chart' );
    
    class OpenChartHelper extends AppHelper
    {
        public $helpers = array( 'Flash','Javascript' );
        
        private $charts = array( );
        
        private $currentChartId;
        
        private $swf = 'flash/open-flash-chart.swf';
        // Default background color
        private $bgColour = '#FFFFFF';
        // Default grid color
        private $gridColour = '#CCCCCC';
        // Default title style
        private $titleStyle = '{color:#EE0000;font-size:40px;text-align:left;padding:0 0 15px 30px;}';
        // Default legend style
        private $legendStyle = '{font-size: 20px; color: #778877}';
        // Default settings
        private $settings = array(
            'width' => 800,
            'height' => 350
        );
        // Default axis ranges
        private $defaultRange = array(
            'x' => array(0, 10, 1),
            'y' => array(0, 10, 1)
        );
        
        public function newChart( $chart = array('type' => 'line', 'params' => array(), 'options' => array()),
                                  $chart_id = 'default',
                                  $values = array(),
                                  $x_options = array(),
                                  $y_options = array() )
        {
            if( empty($this->charts) )
            {
                $this->scripts( );
            }
            if( !isset($chart['params']) )
            {
                $chart['params'] = array();
            }
            $this->currentChartId = $chart_id;
            $this->charts[$chart_id] = $this->newChartType( $chart['type'], $chart['params'] );
            $this->applyOptions( $this->charts[$this->currentChartId], $chart['options'] );
            $this->setChartValues( $chart['type'], $values );
        }
        
        private function newChartType( $typeName, $params )
        {
            if( !is_array( $params ) )
            {
                $params = array( $params );
            }
            extract( array_pad( $params, 3, null ), EXTR_PREFIX_ALL, 'param' );
            return new $typeName( $param_0, $param_1, $param_2 );
        }
        
        private function applyOptions( &$object, $options )
        {
            foreach( $options as $method => $params )
            {
                if( !is_array( $params ) )
                {
                    $params = array( $params );
                }
                extract( array_pad( $params, 3, null ), EXTR_PREFIX_ALL|EXTR_OVERWRITE, 'param' );
                if( method_exists($object, $method) )
                {
                    $object->{$method}( $param_0, $param_1, $param_2 );
                }
                elseif( method_exists($object, 'set_'.$method) )
                {
                    $object->{'set_'.$method}( $param_0, $param_1, $param_2 );
                }
            }
        }
        
        private function setChartValues( $chartType, $values )
        {
            $chartType = ($chartType == 'line') 
                            ? 'dot_value'
                            : $chartType.'_value';
            
            if( class_exists($chartType) )
            {
                foreach( $values as &$value )
                {
                    if( !is_array($value) )
                    {
                        $value = array( 'value' => $value );
                    }
                    if( !is_array($value['value']) )
                    {
                        $value['value'] = array( $value['value'] );
                    }
                    $chartValue = $this->newChartValue( $chartType, $value['value'] );
                    unset( $value['value'] );
                    $this->applyOptions( $chartValue, $value );
                    $this->charts[$this->currentChartId]->append_value( $chartValue );
                }
            }
        }
        
        private function newChartValue( $chartValue, $value )
        {
            extract( array_pad( $value, 4, null ), EXTR_PREFIX_ALL|EXTR_OVERWRITE, 'param' );
            
            return new $chartValue( $param_0, $param_1, $param_2, $param_3 );
        }
        
        /**
        * Method to write the needed javascript functions
        *
        * @access private
        * @return string
        */
        private function scripts( ) 
        {
            $this->Javascript->link( 'json/json2', false );
            $this->Javascript->codeBlock( '
                function to_string(arr) {
                    return JSON.stringify(arr);
                }
                ',
                array('inline' => false)
            );
        }
    }
?>