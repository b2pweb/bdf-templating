<!DOCTYPE html>
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <header>
            <a href="<?= $this->url('home') ?>"><?= $this->_('Home') ?></a>
        </header>
        <?php $this->parts()->output(); ?>
    </body>
</html>
