<?php
class rating{

	public $average = 0;
	public $votes;
	public $status;
	public $table;
	public $trailid;
	public $uid;
	private $path;
	
	function __construct($table, $trailid, $uid){
		try{
			$dbh = mysql_connect('localhost', 'tripnaks_ajtrips', 'kem@r1pe');
			mysql_select_db('tripnaks_joomTrips');
			$this->table = $table;
			$this->trailid = $trailid;
			$this->uid = $uid;
			// check if table needs to be created
			$table_check = mysql_query("SELECT * FROM $this->table WHERE id='1'", $dbh);
			if(!$table_check){
				// create database table
				mysql_query("CREATE TABLE $this->table (id INTEGER PRIMARY KEY AUTO_INCREMENT, rating FLOAT(3,2), ip VARCHAR(15))", $dbh);
				mysql_query("INSERT INTO $this->table (rating, ip) VALUES (0, 'master')", $dbh);
			} else {
				$avg = mysql_query("SELECT average FROM $this->table WHERE trail_id = $this->trailid", $dbh);
				while($row = mysql_fetch_array($avg)){
					$this->average = $row['average'];
				}
//				$this->average = mysql_num_rows($table_check);
//				$this->average = '13.45';
			}
			$count = mysql_query("SELECT COUNT(id) FROM $this->table WHERE trail_id = $this->trailid", $dbh);
			while($row = mysql_fetch_array($count)){
				$this->votes = $row['COUNT(id)'];
			}
		}catch( PDOException $exception ){
				die($exception->getMessage());
		}
		$dbh = NULL;		
	}

	function set_score($score, $trailid, $uid){
		try{
			$dbh = mysql_connect('localhost', 'tripnaks_ajtrips', 'kem@r1pe');
			$max_rating = 5;
			mysql_select_db('tripnaks_joomTrips');
			$voted = mysql_query("SELECT id FROM $this->table WHERE trail_id='$this->trailid' AND uid='$uid'", $dbh);
			if(mysql_num_rows($voted)==0 && $uid > 0){
			        mysql_query("INSERT INTO $this->table (rating, uid, trail_id) VALUES ($score, '$uid', '$trailid')", $dbh) or die(mysql_error());
				$this->votes++;
				
				//cache average in the master row
				$statement = mysql_query("SELECT rating FROM $this->table WHERE trail_id = $this->trailid", $dbh);
				$total = $quantity = 0;
				while($row = mysql_fetch_array($statement)){
					$total = $total + $row['rating'];
					$quantity++;
				}
//				$this->average = round((($total*20)/$quantity),0);
				$this->average = round((($total*100)/($quantity*$max_rating)),0);
				$statement = mysql_query("UPDATE $this->table SET average = $this->average WHERE trail_id=$this->trailid", $dbh);
				
				$this->status = '(thanks!)';
			} else if ($uid == 0){
				$this->status = '(Please login)';
			} else {
				$this->status = '(already rated)';
			}
			
		}catch( PDOException $exception ){
				die($exception->getMessage());
		}
		$dbh = NULL;
	}
}

function rating_form($table, $trailid, $uid){
//echo ($trailid.'-tid rtingfrm<br/>');
	if(!isset($table) && isset($_GET['table'])){
		$table = $_GET['table'];
	}
	if(!isset($trailid) && isset($_GET['trailid'])){
		$trailid = $_GET['trailid'];
	}
	if(!isset($uid) && isset($_GET['uid'])){
		$uid = $_GET['uid'];
	}

	$rating = new rating($table, $trailid, $uid);
	$status = "<div class='score'>
				<a class='score1' href='?score=1&amp;table=$table&amp;trailid=$trailid&amp;uid=$uid'>1</a>
				<a class='score2' href='?score=2&amp;table=$table&amp;trailid=$trailid&amp;uid=$uid'>2</a>
				<a class='score3' href='?score=3&amp;table=$table&amp;trailid=$trailid&amp;uid=$uid'>3</a>
				<a class='score4' href='?score=4&amp;table=$table&amp;trailid=$trailid&amp;uid=$uid'>4</a>
				<a class='score5' href='?score=5&amp;table=$table&amp;trailid=$trailid&amp;uid=$uid'>5</a>
			</div>
	";
	if(isset($_GET['score'])){
		$score = $_GET['score'];
		if(is_numeric($score) && $score <=5 && $score >=1 && ($table==$_GET['table']) && isset($_GET["trailid"]) && isset($_GET["uid"])){
			$rating->set_score($score, $trailid, $uid);
			$status = $rating->status;
		}
	}
	if(!isset($_GET['update'])){ echo "<div class='rating_wrapper'>"; }
	?>
	<div class="sp_rating">
		<!--div class="rating">Rating:</div-->
		<div class="base"><div class="average" style="width:<?php echo $rating->average; ?>%"><?php echo $rating->average; ?></div></div>
		<div class="votes"><?php echo $rating->votes; ?> votes</div><br />
		<div class="status">
			<?php echo $status; ?>
		</div>
	</div>
	<?php
	if(!isset($_GET['update'])){ echo "</div>"; }
}

if(isset($_GET['update'])&&isset($_GET['table'])&&isset($_GET['trailid'])){
	rating_form($_GET['table'], $_GET['trailid'], $_GET['uid']);
}
