## PHP mail() replacement

Drop-in replacement for php mail() which uses the PEAR Mail::send function
instead, allowing for greater flexibility, such as specifying an outbound
SMTP server. 

This replacement function accepts the same parameters in the same
order as the original mail() function, making it very easy to switch one
out for the other.

Note that sending via SMTP requires you specify a "From:" header, or you'll
get an error.

-- 
Copyright 2010-2012 by Tyler Larson <devel@tlarson.com>. All rights reserved.  

This code is distributed according to the terms of the the MIT License,
a copy of which you can find at the following location:  
http://www.opensource.org/licenses/mit-license.html
