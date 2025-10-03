<article>
  <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>
  <div class="text-secondary mb-4">Cập nhật gần đây</div>
  <div>
    <?= nl2br(htmlspecialchars($article['content'])) ?>
  </div>
</article>


