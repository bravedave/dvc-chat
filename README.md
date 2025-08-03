# Chat

Just an iDEA

## Concepts

* Remotes are other people I am talking to
* Local is me and I am Talking to Remotes

    _in development we talk to ourselves so this is quite confusing.._

* This is developed to run _standalone_, which if you want to talk to yourself is great, but you will likely want to talk to someone else, so to activate this - this example does this in **src\app\views\footer.php**

1. A container is created to received the chats - there could be a number of remotes we are talking to
2. Javascript periodically checks for messages and displays the chat dialog
3. A control to _start_ chats in the form of a context menu

## Mitigation

* of course we want the chat as fast as possible, but it falls back to checking every 15 seconds... if we become active, it checks every second ... and then falls back
* we use document.hasFocus to check if the document is active, backgrounded windows don't check ...
