<?php
// Standard inclusions
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

// Define verbal annotations for rating
$verbalAnnotations = array (
	10 => 'maximum, extremely high prestige level for CL, almost all papers are brilliant contributions to the field, best CL proceedings series (from the ones available) top notch research, (almost) always cited',
	9 => 'very high prestige level for CL',
	8 => 'high prestige level for CL',
	7 => 'rather high prestige level for CL',
	6 => 'slightly above medium level though not really prestigious for CL',
	5 => 'medium prestige level for CL few relevant papers appeared in these series',
	4 => 'rather weak prestige level for CL',
	3 => 'weak prestige level for CL',
	2 => 'very weak prestige level for CL',
	1 => 'minimum, extremely low prestige level for CL, almost all papers are superfluous contributions to the field, worst CL proceedings series (from the ones available) poor research, (almost) no citations'
);

// Select the conferences in random order
$conferencesResult = mysql_query("SELECT * FROM `_pollConferences` ORDER BY RAND()");
// Read the MySQL result into an array
$conferences = array();
while($conference = mysql_fetch_object($conferencesResult))
	$conferences[] = $conference;

$author = new stdClass();;
if(is_numeric($_GET['id'])){
	$author = mysql_query("SELECT * FROM `authors` WHERE `author_id` = ".((int) $_GET['id'])." LIMIT 1");
	$author = mysql_fetch_object($author);
}

// Figure out if the visitor is successfully logged in and authenticated and is permitted to participate in poll
/* Conditions to participate in poll
 * 1. author is identified via $_GET['id']
 * 2. the identification hash from the database is the same as given by $_GET['hash']
 * 3. the identified author is a candidate for poll
 * 4. his participation hasn't ended yet due to entering data earlier (within 36 hours) or he hasn't voted yet in poll
 */

if($author->identificationHash == $_GET['hash'] and $author->candidateForPoll == TRUE and ((time() - $author->votedInPoll) < (36 * 60 * 60) or $author->votedInPoll == 0)){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		arsort($_POST['rateConference'], SORT_NUMERIC);
		
		if($author->votedInPoll == 0)
			mysql_query("UPDATE `authors` SET `votedInPoll` = ".time()." WHERE `author_id` = ".$author->author_id);
		else
			mysql_query("DELETE FROM `_pollVotes` WHERE `voteAuthorId` = ".((int) $author->author_id));
		
		mysql_query("INSERT INTO `_pollVotes` (
	`voteAuthorId`,
	`ACL`,
	`ANLP`,
	`CICLING`,
	`COLING`,
	`EACL`,
	`EMNLP`,
	`HLT`,
	`IJCNLP`,
	`LREC`,
	`NAACL`,
	`PACLING`,
	`RANLP`,
	`voteFeedback`
) VALUES (
	".((int) $author->author_id).",
	".((int) $_POST['rateConference']['ACL']).",
	".((int) $_POST['rateConference']['ANLP']).",
	".((int) $_POST['rateConference']['CICLING']).",
	".((int) $_POST['rateConference']['COLING']).",
	".((int) $_POST['rateConference']['EACL']).",
	".((int) $_POST['rateConference']['EMNLP']).",
	".((int) $_POST['rateConference']['HLT']).",
	".((int) $_POST['rateConference']['IJCNLP']).",
	".((int) $_POST['rateConference']['LREC']).",
	".((int) $_POST['rateConference']['NAACL']).",
	".((int) $_POST['rateConference']['PACLING']).",
	".((int) $_POST['rateConference']['RANLP']).",
	'".mysql_real_escape_string(stripslashes($_POST['feedback']))."'
)");
		
		
		$ratedConferences = (string) '';
		while($vote = each($_POST['rateConference']))
			$ratedConferences .= $vote['key'].': '.$vote['value'].nl;

		$text = getTextResource('poll_thankYouMail');
		$text = str_replace('{firstname}', $author->firstname,
			str_replace('{name}', $author->name,
			str_replace('{ratedConferences}', $ratedConferences, $text->textContent)));

		mail_text($author->firstname.' '.$author->name, $author->mail, '[ViP] Thank you!', $text);
		
		$text = getTextResource('poll_thankYouText');
		echo $text->textContent;
	}else{
		if($author->votedInPoll > 0){
			$data = mysql_query("SELECT * FROM `_pollVotes` WHERE `voteAuthorId` = ".$author->author_id." LIMIT 1");
			$data = mysql_fetch_assoc($data);
		}else
			$data = array();
?>

<form action="poll.php?id=<?php echo ((int) $_GET['id'])?>&amp;hash=<?php echo htmlspecialchars($_GET['hash'])?>" method="post">
	<h2>Welcome to the Prestige Assessment Poll for Computational Linguistics Proceedings Series</h2>
	<div class="frame" style="padding: 0.5em">
		<table class="formContainer">
			<tr>
				<td style="width: 60%">
					<p style="font-weight: bold"><?php
		if($author->votedInPoll > 0)
			echo 'Welcome back '.$author->firstname.' '.$author->name.'! Thats where you left off...';
		else
			echo 'Hello '.$author->firstname.' '.$author->name.'!';?></p>
<?php
		$text = getTextResource('poll_welcomeText');
		echo $text->textContent;
?>

					<table class="dataContainer">
						<tr>
							<th style="font-size: 0.8em">Value</th>
							<th style="font-size: 0.8em">Verbal annotation</th>
						</tr>

<?php
foreach($verbalAnnotations as $identification => $annotation){
?>

						<tr>
							<td style="font-weight: bold; width: 10%; font-size: 0.8em"><?php echo $identification?></td>
							<td style="font-size: 0.8em"><?php echo $annotation?></td>
						</tr>

<?php
}
?>

					</table>
				</td>
				
				<td style="width: 40%; border: 2px dashed #0a0">
					<h3>Your vote here</h3>
					<table class="dataContainer">
						<tr>
							<th style="width: 70%">Conference</th>
							<th style="width: 30%">Rating</th>
						</tr>
<?php
foreach($conferences as $conference){
?>

						<tr>
							<td>
								<a href="<?php echo $conference->conferenceURL?>">
									<img src="<?php echo PATH?>/_resources/images/internet.png" alt="Website of <?php echo $conference->conferenceAcronym?>" />
								</a>
								<label for="rateConference_<?php echo $conference->conferenceAcronym?>">
									<acronym title="<?php echo $conference->conferenceName?>"><?php echo $conference->conferenceAcronym?></acronym>
								</label>
							</td>
							<td>
								<select id="rateConference_<?php echo $conference->conferenceAcronym?>" name="rateConference[<?php echo $conference->conferenceAcronym?>]">
<?php
	for($j = 0; $j <= 10; $j++){
?>

									<option value="<?php echo $j?>"<?php if($j == $data[$conference->conferenceAcronym]) echo ' selected="selected"'?>><?php echo $j?></option>

<?php
	}
?>
								</select>
							</td>			
						</tr>
<?php
}
?>

					</table>
					<h3>Your comments or feedback here</h3>
					<textarea id="feedback" name="feedback" rows="10" cols="40" style="width: 100%"><?php echo $data['voteFeedback']?></textarea>
				</td>
				
			</tr>
		</table>
		</div>
		
		<div class="submit">
			<input type="submit" value="vote!" />
		</div>
	</form>

<?php
	}
}else{
?>

<form action="<?php echo PATH?>/poll/" method="get" style="margin: 0 auto; width: 500px">
	<h2>Please enter your login data!</h2>
		
	<div class="frame">
		<p>
			If you have any poll-related questions, please take a look at the <a href="<?php echo PATH?>/faq.php">FAQ section</a> before sending us a mail!
		</p>

<?php
	if(!empty($_GET['id']) and !empty($_GET['hash'])){
		if(is_object($author) and ((time() - $author->votedInPoll) > (36 * 60 * 60) and $author->votedInPoll != 0)){
?>

		<strong class="failure" style="display: block">Your account has been closed due to expiration of voting time!</strong>

<?php
		}else{
?>

		<strong class="failure" style="display: block">Sorry, your entered data is not a valid login!</strong>

<?php
		}
	}
	
	if((!empty($_GET['id']) and empty($_GET['hash'])) or (empty($_GET['id']) and !empty($_GET['hash']))){
?>

		<strong class="failure" style="display: block">You need to fill, both fields in order to log in!</strong>

<?php
	}
?>

		<div style="float: right; width: 70%">
			<label for="hash" class="block">Your hash</label>
			<input type="text" id="hash" name="hash" size="40" style="width: 100%" />
		</div>

		<div style="float: left; width: 20%">
			<label for="id" class="block">Your ID</label>
			<input type="text" id="id" name="id" size="5" style="width: 100%" />
		</div>

		<br style="clear: both" />
	</div>
		
	<div class="submit"><input type="submit" value="login" /></div>
</form>

<?php
}
require '../__close.php';
require '../_footer.php';
?>