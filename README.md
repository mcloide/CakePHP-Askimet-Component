CakePHP Askimet Component
=========================

Askiment Spam Check for CakePHP

Have all methods necessary to connect with the Askimet api. 

- verify key: Key verification need only be used if you will have different users with their own Akismet licenses using your platform. This call will verify that they are using a valid API key.
- comment check: Comment check is used to ask Akismet whether or not a given post, comment, profile etc. is spam
- submit spam and submit ham: Submit Spam and Submit Ham are follow-ups to let Akismet know when it got something wrong (missed spam and false positives). These are also very important and you shouldn’t develop using the Akismet API without a facility to include reporting missed spam and false positives.

For more info on what parameters to pass on each method, check: http://akismet.com/development/api/

Requires
========
- An Askimet API key: The development key can be got from: http://akismet.com/contact/ and the Askimet API key can be got from: https://akismet.com/signup/ .

Current Version
===============
1.0 Stable

Examples
========

{code}
  	// verify key
    var_dump($this->Askimet->verify_key(array('key' => 'yourKey')));
    
		$params = array(
			'key' => 'yourKey',
			'comment_author' => 'viagra-test-123',
			'comment_author_email' => 'viagratest123@spamcheck.com',
			'comment_author_url' => '',
			'comment_content' => 'testing spam check with askimet',
			'permalink' => 'http://localhost',
			'comment_type' => 'comment',
		//	'debug' => true // enable this flag if you get an error response from comment check
		);
		var_dump($this->Askimet->comment_check($params));
		var_dump($this->Askimet->submit_spam($params));
    
		$params = array(
			'key' => 'yourKey',
			'comment_author' => 'a-valid-author',
			'comment_author_email' => 'a-valid-email@validomain.com',
			'comment_author_url' => '',
			'comment_content' => 'testing spam check with askimet',
			'permalink' => 'http://localhost',
			'comment_type' => 'comment'
		);
		var_dump($this->Askimet->comment_check($params));
		var_dump($this->Askimet->submit_ham($params));
{code}


Notes
=====
- Currently it is structured to work with CakePHP v2.x, but I believe that with a few tweaks it can easily works with previous versions
