<?php

namespace source\Models\Faqs;

use Source\Core\Connect;

class Faq
{
    private ?int $id;
    private ?int $categoryId;
    private ?string $question;
    private ?string $answer;

    public function __construct(?int $id = null, ?int $categoryId = null, ?string $question = null, ?string $answer = null)
    {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->question = $question;
        $this->answer = $answer;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
    public function getQuestion(): string
    {
        return $this->question;
    }
    public function getAnswer(): string
    {
        return $this->answer;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setCategoryId(int $categoryId)
    {
        $this->categoryId = $categoryId;
    }
    public function setQuestion(string $question)
    {
        $this->question = $question;
    }
    public function setAnswer(string $answer)
    {
        $this->answer = $answer;
    }

    public function listAll(): array
    {
        $query = "SELECT * FROM faqs as f
                  JOIN faqs_categories as c ON f.faqs_category_id = c.id
                  GROUP BY c.name";
        $stmt = Connect::getInstance()->query($query);
        return $stmt->fetchAll();
    }

    public function listById(int $id): object|bool
    {
        $query = "SELECT * FROM faqs as f
                  JOIN faqs_categories as c ON f.faqs_category_id = c.id
                  WHERE f.id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function insert(): array|bool
    {
        $query = "INSERT INTO faqs (faqs_category_id, question, answer) VALUES ($this->categoryId, $this->question, $this->answer)";
        $stmt = Connect::getInstance()->query($query);
        if ($stmt->rowCount() > 0) {
            $this->id = Connect::getInstance()->lastInsertId();
            $query = "SELECT * FROM faqs as f
                      JOIN faqs_categories as c ON f.faqs_category_id = c.id
                      WHERE f.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }

    public function update(array $data): array|bool
    {
        $query = "SELECT * FROM faqs WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->bindValue(':id', $data['faqId']);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            return false;
        }
        $stmt->fetch();

        $query = "UPDATE faqs SET faqs_category_id = :faq_cat_id, question = :question, answer = :answer WHERE id = :id";
        $stmt = Connect::getInstance()->prepare($query);
        $stmt->execute([
            ":faq_cat_id" => $data['faqs_category_id'],
            ":question" => $data['question'],
            ":answer" => $data['answer'],
            ":id" => $data['faqId']
        ]);
        if ($stmt->rowCount() > 0) {
            $query = "SELECT * FROM faqs as f
                      JOIN faqs_categories as c ON f.faqs_category_id = c.id
                      WHERE f.id = :id";
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->bindParam(':id', $data['faqId']);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        }
        return false;
    }
}