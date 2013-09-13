<?php include_once("./inc/header.php"); ?>
    
    <?php
	// if form has been submitted, process inputs
	if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) :
		// initialize storage vars
		$name = $email = $tel = $reason = $severity = $message = $status = '';
		
		// validate form fields
		if($_POST['name'] == '') { // check name field for contents (required)
			$status .= 'Please enter a name.<br/>';
		} else { // if name is filled in, strip everything that isn't allowed in a string
			$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
			
			$status .= ($name == '') ? 'Please enter a valid string for name.<br/>' : ''; // if there's nothing left after sanitization add an error
		}
		
		if($_POST['email'] == '') { // check email for contents
			$status .= 'Please enter an email address I can reach you at.<br/>';
		} else { // if email is filled in, strip everything that isn't allowed in an email address and check for valid address
			$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // sanitize the email input

			if($email != '') { // if after sanitization there is something left, continue validating. otherwise add error to status
				$email = (filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : ''; // if valid email address, store it for use, otherwise clear the email var
			} else {
				$status .= 'The email you entered is not a vaild email address. Use this format: host@domain.tld<br/>'; // if invalid email address, add error to status
			}
		}
		
		if($_POST['tel'] != '') { // if they gave a telephone number (optional) check it for validity
			$tel = $_POST['tel'];
			
			if(!preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $tel)) { // if the number is not in this format: (1-)123-456-7890 add error and clear $tel
				$status .= 'The telephone you entered is not valid. Use this format 1-585-475-2271. The country code is optional.<br/>';
				$tel = '';
			}
		}
		
		if($_POST['message'] == '') { // check message field for contents (required)
			$status .= 'You forgot to type me a message!<br/>';
		} else { // if message is filled in, strip everything that isn't allowed in a string
			$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
			
			$status .= ($message == '') ? 'Please enter a valid string for message.<br/>' : ''; // if there's nothing left after sanitization add an error
		}
		
		$reason = $_POST['reason'];
		$severity = $_POST['severity'];
		
		// if there are no errors, send the email and show a confirmation message
		if($status == '') {
			$body = "From: " . $name . "\n";
			$body .= "Reply to: " . $email . "\n";
			$body .= "Telephone: " . $tel . "\n";
			$body .= "Reason: " . $reason . "\n";
			$body .= "Severity: " . $severity . "\n";
			$body .= "\n";
			$body .= "Message: " . $message;
			
			if(mail("vxi6514+539contact@rit.edu", "[539 - Project1 Contact] $name - $reason", $body))
				$success = 1;
			else
				'<b>There was an error sending the email.</b>';
		}
	endif;
	
	if($success && $status == '') : // if sending the email happened without errors and there are no errors to display, show confirmation and hide form
	?>
    
    <div id="status" class="good">
    	<p>The message was sent successfully. Thanks for contacting me!</p>
        <p>You can go <a href="./index.php">home</a> or <a href="./news.php">back to reading the news</a>.</p>
    </div>
    
    <?php else: ?>
    
	<?php if($status != '') : // if there are errors, display them ?>
    <div id="status" class="bad">
            <?php echo $status; ?>
    </div>
    <?php endif; ?>
    
    <h3 id="formtitle">Contact Me</h3>    
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="contact">
        <label for="name">Your Name *</label><input id="name" name="name" placeholder="Vlad Ionescu" required />
        <label for="email">Your Email *</label><input id="email" name="email" type="email" placeholder="vxi6514@rit.edu" required />
        <label for="tel">Contact Telephone</label><input id="tel" name="tel" type="tel" placeholder="(585) 475-2271" required />
        <label for="reason">Reason For Correspondence</label>
        <select id="reason" name="reason">
        	<option value="question">I have a question for you</option>
            <option value="suggestion" selected>I have a suggestion you might like</option>
            <option value="bug">I've found a bug!</option>
            <option value="testing">I'm just testing this form</option>
        </select>
        <label for="severity">How Urgent Is This Message?</label>
        <select id="severity" name="severity">
        	<option value="low" selected>Low - immediate response not necessary</option>
            <option value="medium">Medium - response needed in a timely manner</option>
            <option value="high">High - important issue needs attention</option>
            <option value="critical">Critical - drop everything else</option>
        </select>
        <label for="message">Message *</label><textarea id="message" name="message"></textarea>
        
        <input id="submit" name="submit" type="submit" value="SUBMIT" />
        <input id="reset" name="reset" type="reset" value="CLEAR" />
    </form>
    
	<?php endif; ?>

<?php include_once("./inc/footer.php"); ?>