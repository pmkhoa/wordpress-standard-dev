<?php get_header(); ?>

  <section id="main" role="main">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <?php the_content(); ?>
      Go to <a href="/sample-page/">next page</a>.

    <?php endwhile; endif; ?>

  </section> <!-- /#main -->

<?php get_footer(); ?>
