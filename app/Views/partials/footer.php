        </div>
    </div>
</div>
<script src="/js/jquery-2.2.4.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/jquery.maskMoney.js"></script>
<script src="/js/scripts.js"></script>
<script src="/js/footer.js"></script>
<?php foreach (($data['scripts'] ?? []) as $script): ?>
<script src="<?= htmlspecialchars((string) $script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
