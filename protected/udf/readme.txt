   /*
     * Corresponding to the MySQL function "postRank" (underneath) - in /webapp/protected/udf folder postrank.c

      gcc $(mysql_config --cflags) -shared -fPIC -o postrank.so postrank.c
      cp postrank.so /usr/lib64/mysql/plugin/
	drop function postrank;
	CREATE FUNCTION postrank RETURNS REAL SONAME 'postrank.so';

        select *, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank FROM tbl_posts order by rank DESC

	同理。。。我们可以为评论创建一个方程，然后用
	select *, commentrank(up, down) as rank FROM tbl_comments order by rank DESC


//下面是mysql里面的function，仅供展示，现在只需要用udf
DROP FUNCTION IF EXISTS postrank;

CREATE DEFINER=`root`@`localhost` FUNCTION `postrank`(ups INT(10),downs INT(10),d INT) RETURNS double
    DETERMINISTIC
RETURN ROUND(LOG10(GREATEST(ABS(ups-downs), 1))*SIGN(ups-downs) + (d - 1444000000) / 64800.0, 7);
    */


    protected function postRank($fake_ups, $ups, $downs, $date){	//hot

	$ups = $ups + round(0.02 * $fake_ups);	//50 fake = 1 real

	$score = $ups - $downs;
	$order = log10(max(abs($score), 1));
	if($score > 0){
		$sign = 1;
	}else if($score < 0){
		$sign = -1;
	}else{
		$sign = 0;
	}
	$seconds = $date - 1444000000;		//1444000000 is the earliest post

	return round($sign * $order + $seconds / 86400, 7);	//86400 

	//86400 is the number of seconds in 24 hours.  The way the algo works is that something needs to have 10 times as many points to be "hot" as something 24. hours younger.  In other words, that's the boundary for the log.
	//reddit is using 12.5 => 45000
	//we can make it higher if we found the algorithm preferred new stuff way too much... update MySQL as well for this.
	//https://www.quora.com/Where-do-the-constants-1134028003-and-45000-come-from-in-reddits-hotness-algorithm

    }



    protected function commentRank($ups, $downs){	//comment

	$n = $ups + $downs;
	if($n == 0){
		return 0;
	}

	$z = 1.281551565545; //80% confidence, 1.0 = 85 conf, 1.6 = 95 conf.
	$p = $ups / n;

	$left = $p + 1/(2*$n)*$z*$z;
	$right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
	$under = 1+1/$n*$z*$z;
	
	return ($left - $right) / $under;
    }

