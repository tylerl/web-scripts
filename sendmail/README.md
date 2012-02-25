## PHP Mailer

Yet another mailer script for PHP. This is about as dead-simple as you can get.
The code is fairly self-explanatory: Specify sender, recipient, subject, and 
format the message body. And that's about it.

Features include:

  * Redirect after mail processing (to success/failure URL)
  * Hides mailer error messages if you specify a failure URL. Otherwise it shows them
  * SPAM-check value; can redirect to a "spam error" page if a given field
    doesn't match the expected value
  * Generates both HTML and plain-text email as a fallback. Plain-text is generated simply
    by stripping the tags from the HTML version. Plain-text is required for some spam filters. 
  * Relatively simple templating structure (replace `"{{var}}"` with `$form['var']`) to simplify
    form generation for non-developers
    
-- 
Copyright 2010-2012 by Tyler Larson <devel@tlarson.com>  
All rights reserved.  
This code is distributed according to the terms of the the MIT License,
a copy of which you can find at the following location:  
http://www.opensource.org/licenses/mit-license.html
