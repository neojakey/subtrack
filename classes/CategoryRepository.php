<?php
/**
 * CategoryRepository.php — Subscription categories
 */
class CategoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        return $this->pdo->query(
            'SELECT * FROM categories ORDER BY sort_order ASC'
        )->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findBySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /** Return categories keyed by id for quick lookup. */
    public function findAllKeyed(): array
    {
        $rows = $this->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['id']] = $row;
        }
        return $map;
    }
}
