<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

<div class="subNav" style="width: 20%">
	<strong>Exclude from list</strong><br />
	<a href="sendInvitationMails.php?exclude=OK">Not voted, account open</a><br />
	<a href="sendInvitationMails.php?exclude=voted">Voted, account open</a><br />
	<a href="sendInvitationMails.php?exclude=none">show all</a>
</div>
<div class="subNav" style="width: 20%"><a href="sendInvitationMails.php?exclude=<?php echo htmlspecialchars($_GET['exclude'])?>&amp;action=send">Send the mails <?php echo returnIcon('email')?></a></div>

<h2>Send invitation mails for poll</h2>

<?php
if(empty($_GET['exclude']))
	$_GET['exclude'] = 'voted';

$candidates = NULL;
switch($_GET['exclude']){
	case 'OK':
		$candidates = mysql_query("SELECT * FROM `authors` WHERE `candidateForPoll` = 1 AND `votedInPoll` != 0 ORDER BY `name`, `firstname`");
	break;

	default:
	case 'voted':
		$candidates = mysql_query("SELECT * FROM `authors` WHERE `candidateForPoll` = 1 AND `votedInPoll` = 0 ORDER BY `name`, `firstname`");
	break;

	case 'none':
		$candidates = mysql_query("SELECT * FROM `authors` WHERE `candidateForPoll` = 1 ORDER BY `name`, `firstname`");
	break;
}

if(mysql_num_rows($candidates) > 0){
?>

<div>
	These are the authors that have been marked as candidates for the  poll.<br />
	Listing <?php echo mysql_num_rows($candidates)?> author(s)...
</div>
<table class="dataContainer" style="margin: 2em 0">
	<tr>
		<th style="width: 40%">Name</th>
		<th style="width: 30%">Mail</th>
		<th style="width: 30%">Status</th>
	</tr>

<?php
	$i = (int) 0;
	$candidate = new stdClass();
	while($candidate = mysql_fetch_object($candidates)){
		$candidate->status = '<span class="success">Not voted, account open</span>';
		if($candidate->votedInPoll != 0){
			$candidate->status = '<span class="success">Has voted, account open</span><br />'.
				secondsToReadableString($candidate->votedInPoll + (36 * 60 * 60) - time()).' before account is closed';

			if((time() - $candidate->votedInPoll) > (36 * 60 * 60))
				$candidate->status = ' <span class="failure">account closed</span>';
		}

		if($_GET['action'] == 'send'){
			$text = getTextResource('poll_inviteMail');
			$text = str_replace('{firstname}', $candidate->firstname,
				str_replace('{name}', $candidate->name,
				str_replace('{id}', $candidate->author_id,
				str_replace('{hash}', $candidate->identificationHash, $text->textContent))));

			if(mail_text($candidate->firstname.' '.$candidate->name, $candidate->mail, '[ViP] Invitation to participate in poll', $text))
				$candidate->status = '<span class="success">sent mail</span>';
		}
?>

	<tr>
		<td>
			<strong><?php echo $candidate->name?></strong>,
			<?php echo $candidate->firstname?>
		</td>
		<td><?php echo $candidate->mail?></td>
		<td><?php echo $candidate->status?></td>
	</tr>

<?php
	}
?>

</table>
<?php
}

require '../__close.php';
require '../_footer.php';
?>