<?php

/*-
 * Copyright (c) 2010 by Exos (exos@exodica.com.ar)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of copyright holders nor the names of its
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
 * TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

class EasyCry {

	public $bloksize = 8;
	public $compress = true;
	public $binary = false;

	public static function caesar ($char, $level = 3,$invert = false) {

		if (is_string($level)) {
			$level = ord($level);
		}

		$c = ord($char);

		if ($invert) {
			$c -= $level;
			if ($c < 0) $c += 255;
		} else {
			$c += $level;
			if ($c > 255) $c -= 255;
		}	

		return chr($c);

	}

	public static function powcaesar ($char, $level = 3, $invert = false) {

		if (is_string($level)) {
                        $level = ord($level);
                }

                $c = ord($char);

		$r = (int) ($level/3)^2 + $level;

		if ($invert) {
			$c -= $r%255;
		} else {
			$c += $r%255;
		}

		return chr($c);

	}

	public static function getOrderRange ($min, $max, $seed = null) {

		if (is_null($seed)) $seed = microtime(true);

		if (is_string($seed)) $seed = abs(crc32($seed));

		srand($seed);

	 	$numbers = array();
		$cnt = $max-$min;

	 	while (count($numbers) <= $cnt) {
			$n = (string) rand($min,$max);
			if (in_array($n, $numbers)) continue;
			$numbers[] = $n;
	 	}
	 
	 	return $numbers;
	}

	public function encode ($data, $clave) {

		if ($this->compress) $data = gzdeflate($data,9);

		if ($this->bloksize < 1|| $this->bloksize > 49) {
			throw new Exception ("Block size tiene que estar entre 1 y 255");
		}

		$time = dechex(time());
		$size = dechex(strlen($data));
		$msig = substr(md5($time.$size.$clave),3,4);
		$sum  = md5($data);

		$meta = array (
			"t:$time",
			"c:$size",
			"h:$msig",
			"s:$sum",
			'z:' . ( $this->compress ? '1' : '0')
		);

		$meta = substr(implode('|',$meta). ';' . hash('sha512', $sum . microtime(true) ),0,128);

		$data = $meta.$data;

        	$c = strlen($data);

        	$dc = (int) ($c / $this->bloksize) +1;
        	$dc *= $this->bloksize;

                $data = substr($data.md5($sum . microtime(true)),0,$dc);

		$asum = md5(md5($clave).hash('sha512',$data));

		$ao = md5($asum.$clave);
		$binfo = self::powcaesar(chr($this->bloksize) , $ao{7} );

		$parshe = hash('sha512',$clave . $asum );

		$pdata = "";
		$phc = 0;

		for ($i=0; $i < $dc; $i+=$this->bloksize) {
	    		$tdata = substr($data,$i,$this->bloksize);
			
			

	    		$order = self::getOrderRange(0,$this->bloksize-1,substr($parshe.$parshe,$phc,4));

	    		for ($j=0; $j < $this->bloksize;$j++) {

				$inc = hexdec(substr($parshe,$phc,2));
				
		  		$pdata .= self::powcaesar(
					$tdata{$order[$j]},
					$inc
				);
		  		
				$phc+=2;
		  		
				if ($phc >= 126) {
					$phc = 0;
					$parshe = hash('sha512',$clave . $asum . $parshe );
				}

	   		}
		}

		$isum = "";

		for ($i = 0; $i < strlen($asum); $i+=2) {
			$isum .= chr(hexdec( substr($asum,$i,2) ) );
		}		

		return $this->binary? $isum.$binfo.$pdata : base64_encode($isum.$binfo.$pdata);

	}


	public function decode ($content, $clave) {

		if (!$this->binary) $content = base64_decode($content);

		$binfo = $content{16};
		$isig = substr($content,0,16);

		$sig = "";

                for ($i = 0; $i < strlen($isig); $i++) {
                        $sig .=  substr( '0' . dechex(ord($isig{$i}) ) ,-2);  ;
                }


		$ao = md5($sig.$clave);
                $bs = ord( self::powcaesar($binfo , $ao{7}, true ) );

		$bs = ord( self::powcaesar( $binfo , $ao{7} , true  ) );

		$data = substr($content,17);

        	$parshe = hash('sha512',$clave . $sig);
	
		$c = strlen($data);
 	  	$dc = (int) ($c / $bs) ;
		$dc *= $bs;

		$pdata = "";
		$phc = 0;

		for ($i=0; $i < $c; $i+=$bs) {
	    		$tdata = substr($data,$i,$bs);
			$order = self::getOrderRange(0,$bs-1,substr($parshe.$parshe,$phc,4));

	    		$buf = str_repeat("a",$bs);

			foreach($order as $k => $n) {
				
				$inc = hexdec(substr($parshe,$phc,2));

				$buf{$n} = self::powcaesar(
					$tdata{$k},
					$inc
					,true);

				$phc+=2;

				if ($phc >= 126) {
					$phc = 0;
					$parshe = hash('sha512',$clave . $sig . $parshe );
				}
	   		}
			$pdata.=$buf;
		}

		$csig = md5(md5($clave).hash('sha512',$pdata));		

		$meta = substr($pdata,0,strpos($pdata,';')) ;

		$ameta = explode('|',$meta);

		$dmeta = array();

		foreach ($ameta as $val) {
			$val = explode(':',$val);
			$dmeta[$val[0]] = $val[1];
		}



       		$hora = hexdec($dmeta['t']);
        	$size = hexdec($dmeta['c']);
        	$mdsig = $dmeta['h'];
		$sum = $dmeta['s'];
		$compress = (bool) $dmeta['z'];

		$data = substr($pdata,128,$size);
		
		$asum = md5(md5($clave).hash('sha512',$pdata));

		if ($asum != $sig) {
			throw new Exception("Integridad de los datos rota");
		}

		if ($mdsig != substr(md5(dechex($hora).dechex($size).$clave),3,4)) {
			throw new Exception("Metadata alterada");
		}

		if (md5($data) != $sum) {
			throw new Exception("Checksum no coincide, posible data alterada");
		}

		if ($compress) $data = gzinflate($data);

		return array(
			'data' => $data,
			'meta' => $meta,
		);
	}

}


