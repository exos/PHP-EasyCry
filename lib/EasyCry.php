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

		$time = dechex(time());
		$size = dechex(strlen($data));
		$msig = md5($time.$size);

		$meta = array (
			't'=>$time,
			'c'=>$size,
			'h'=>$msig,
		);
	
		$meta = substr(serialize($meta). chr(0) . hash('sha512',microtime(true)*rand(1,999)),0,128);

		$data = $meta.$data;

        	$c = strlen($data);

        	$dc = (int) ($c / $this->bloksize) +1;
        	$dc *= $this->bloksize;

                $data = substr($data.md5(microtime(true)),0,$dc);

		$sig = hash("sha256",$data);

		$parshe = hash('sha512',$clave . $sig);

		$pdata = "";
		$phc = 0;

		for ($i=0; $i < $dc; $i+=$this->bloksize) {
	    		$tdata = substr($data,$i,$this->bloksize);
	    		$order = self::getOrderRange(0,$this->bloksize-1,substr($parshe.$parshe,$phc,4));

	    		for ($j=0; $j < $this->bloksize;$j++) {
		  		$pdata .= self::caesar(
					$tdata{$order[$j]},
					$parshe{$phc}
				);
		  		$phc++;
		  		if ($phc >= 128) $phc = 0;
	   		}
		}

		return $this->binary? $sig.$pdata : base64_encode($sig.$pdata);

	}


	public function decode ($content, $clave) {

		$content = base64_decode($content);
		$sig = substr($content,0,64);

		$data = substr($content,64);

        	$parshe = hash('sha512',$clave . $sig);
	
		$c = strlen($data);
 	  	$dc = (int) ($c / $this->bloksize) ;
		$dc *= $this->bloksize;
		$pdata = "";
		$phc = 0;

		for ($i=0; $i < $c; $i+=$this->bloksize) {
	    		$tdata = substr($data,$i,$this->bloksize);
			$order = self::getOrderRange(0,$this->bloksize-1,substr($parshe.$parshe,$phc,4));

	    		$buf = str_repeat("a",$this->bloksize);

			foreach($order as $k => $n) {
				$buf{$n} = self::caesar(
					$tdata{$k},
					$parshe{$phc}
					,true);
				$phc++;
				if ($phc >= 128) $phc = 0;
	   		}
			$pdata.=$buf;
		}

		$csig = hash('sha256',$pdata);
		
		$meta = unserialize( substr($pdata,0,strpos($pdata,chr(0))) );
       		$hora = hexdec($meta['t']);
        	$size = hexdec($meta['c']);
        	$mdsig = $meta['h'];

		$data = substr($pdata,128,$size);


		if ($this->compress) $data = gzinflate($data);

		if (false && $mdsig !== md5($meta['t'].$meta['c'])) {
			throw new Exception ("Error en la metada, posible informacion alterada");
		}

		if (false && $sig !== $csig) {
			throw new Exception ("Error en el firmado, posible informacion alterada");
		}

		return array(
			'data' => $data,
			'meta' => $meta,
		);
	}

}


