There is a lack of label-based file storage solutions. It is convenient in Gmail to apply labels to our conversations, rather than put them in folders. Why can't we have the same functionality for storing our files?

This project provides that functionality.

The user uploads files to the server through a web interface, specifying labels for each file. The files are put into a compressed archive and data about each file is stored in a database.

Users can specified whether the files being uploaded can be accessed by any other user (public), or only themselves (private).

To find particular files that have been stored, the user can apply filters based on labels.

No document-editing features are provided, simply storage and retrieval.

This project is built primarily in PHP.