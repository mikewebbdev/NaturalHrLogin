# Basic login system

## Installation

Add this folder into your server directory.
You will need to upload the included **naturalhr.sql** file to your mysql server. Alternatively, you can run the following command on your own database, but you will need to update the database name in the **db/loginhandler.php**, **index.php** and **logout.php** files.
Finally, edit the information in **conn.php** with the user/pass for your mysql server.

```
CREATE TABLE IF NOT EXISTS `nhrusers` (
  `userID` varchar(32) NOT NULL,
  `userFirst` varchar(11) NOT NULL,
  `userSecond` varchar(11) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userPass` varchar(255) NOT NULL,
  `userStatus` smallint(11) NOT NULL,
  `userRole` smallint(11) NOT NULL,
  `userRecovery` varchar(11) NOT NULL,
  PRIMARY KEY (`userID`)
)
```

## How it works

A user has to register before they can log in. If they try to access **index.php** without logging in they will be redirected back to the login page. To register, a user must submit their name, email address, and a password or at least 8 letters.

If the email already exists in the system, the register process will fail. If the email is new, a unique ID is created, their password is hashed, and the user is added to the system.

Once registered, the user can then log in.

When logging in, the server registers a cookie with a random string on the user's browser. This is also stored against the user's record in the database. This means that we can track login attempts.

Once logged in, the cookie is checked. If the cookie in the browser no longer matches the string in the database, the user is logged out and the database reset.

Once logged in, the user has the ability to upload files. On their first file upload, each user is generated a unique upload folder (based on userID). This means that they will see only their uploaded files. When they have uploaded files, they will be able to see the files in a list. They can click on the filename to view the file in a new tab/window.

The user can sign out when they are finished, which will remove the cookie from the browser and also the random string from the database. This makes it more difficult for a malicious user to access the system.


## Improvements

Currently, there is no mail function. (I could have added this with Gmail, but it's better not to expose private passwords!) With access to a mail server, I would add a mail section to the registration process. This would allow a verification step before a user is allowed to access the system fully. It would also allow a user to reset their password in a secure fashion.

The uploaded-file system could be improved with a database link - this would allow a user to see more information about each file, as well as add descriptions or titles.

As a business to business company, it would be useful to add an option to register with LinkedIn and/or Google OAuth paths, instead of having to register manually.
