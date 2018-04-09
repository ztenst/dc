<?php
namespace app\helpers;
/**
 * 时间戳类
 * @author weibaqiu
 * @version 2015-05-08
 */
class Timestamp
{
	/**
	 * 获得指定日期的星期
	 * @param  integer $timestamp 时间戳，默认为当前时间
	 * @return string             返回中文数字（一、二、三、四、五、六、天），若指定时间戳，返回时间戳所指定的星期，否则返回当前的星期。
	 */
	public static function getWeek($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		$week = array(0=>'天',1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六');

		return $week[date('w', $timestamp)];
	}

	/**
	 * 获得指定日期0点时间戳
	 * @param  integer $timestamp  指定某个时间点的时间戳，可选
	 * @return integer             返回指定时间戳那天的0点时间戳，若无参数则默认返回此时此刻今天0点的时间戳
	 */
	public static function getDayBeginTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		return mktime( 0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp) );
	}

	/**
	 * 获得指定日期23:59:59时间戳
	 * @param  integer $timestamp  指定某个时间点的时间戳，可选
	 * @return integer             返回指定时间戳那天的23:59:59时间戳，若无参数则默认返回此时此刻今天23:59:59的时间戳
	 */
	public static function getDayEndTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		return mktime( 23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp) );
	}

	/**
	 * 获得本周的开始时间戳
	 * @return integer 时间戳
	 */
	public static function getWeekBeginTime()
	{
		return mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
	}

	/**
	 * 获得本周的结束时间戳
	 * @return integer 时间戳
	 */
	public static function getWeekEndTime()
	{
		return mktime(23,59,59,date('m'),date('d')+(7-date('w')),date('Y'));
	}

        /**
	 * 获得上周的开始时间戳
	 * @return integer 时间戳
	 */
	public static function getLastWeekBeginTime()
	{
            return mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
	}

        /**
	 * 获得上周的结束时间戳
	 * @return integer 时间戳
	 */
	public static function getLastWeekEndTime()
	{
        return mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
	}

	/**
	 * 获得指定时间戳所在月的开始时间戳
	 * @return integer 时间戳
	 */
	public static function getMonthBeginTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		return mktime(0,0,0,date('m', $timestamp),1,date('Y', $timestamp));
	}

	/**
	 * 获得本月的结束时间戳
	 * @return integer 时间戳
	 */
	public static function getMonthEndTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		return mktime(23,59,59,date('m', $timestamp),date('t', $timestamp),date('Y', $timestamp));
	}

	/**
	 * 获得上个月的开始时间戳
	 * @return integer 时间戳
	 */
	public static function getLastMonthBeginTime()
	{
		$lastMonth = (date('m')-1)<=0 ? 12 : (date('m')-1);
		return mktime(0,0,0,$lastMonth,1,date('Y'));
	}

	/**
	 * 获得未来30后的结束时间
	 * @return integer 时间戳
	 */
	public static function getThirtyDaysEndTime()
	{
		return (self::getDayEndTime() + 86400 * 30);
	}

	/**
	 * 获得指定时间戳所在季度的开始时间戳
	 * @param  integer $timestamp 指定的时间戳
	 * @return integer 时间戳
	 */
	public static function getSeasonBeginTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		$season = ceil(date('n', $timestamp)/3);//所在季度
		return mktime(0,0,0,$season*3-2,1,date('Y'));
	}

	/**
	 * 获得指定时间戳所在季度的结束时间戳
	 * @param  integer $timestamp 指定的时间戳
	 * @return integer 时间戳
	 */
	public static function getSeasonEndTime($timestamp=0)
	{
		$timestamp = $timestamp ? $timestamp : time();
		$season = ceil(date('n', $timestamp)/3);//所在季度
		return mktime(23,59,59,$season*3,date('t'),date('Y'));
	}
}
