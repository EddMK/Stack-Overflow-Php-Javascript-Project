<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ask a public question</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/style.css" rel="stylesheet" type="text/css"/>
		<script src="https://kit.fontawesome.com/9f16cf7640.js" crossorigin="anonymous"></script>
    </head>
    <body>
		<?php include('menu.html'); ?>
        <div class="mainAsk">
            <form id="message_form" action="post/ask" method="post">
                <label for="title">Title</label>
				<input id="title" name="title" type="text" value="<?= $title ?>">
				<label for="choix[]">Tags</label>
					<?php foreach($tags as $tag){ ?>
						<input type="checkbox" name="choix[]" value=<?= $tag->get_tagId() ?> > <?= $tag->tagName ?>  
					<?php } ?>
				<label for="body">Body</label>
				<textarea id="body" name="body" rows='3'><?= $body ?></textarea>
				<input id="post" type="submit" value="Post">
            </form>          
        </div>
		<?php if (count($errors) != 0){ ?>
                <div class='errors'>
                    <p>Errors :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
		<?php } ?>
    </body>
</html>