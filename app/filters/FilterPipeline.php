<?php
declare(strict_types=1);

/**
 * Kalite filtrelerini sırayla çalıştırır (kayıttan ÖNCE). İlk reddeden filtre item'ı
 * eler — item DB'ye hiç yazılmaz. Sıra ucuzdan pahalıya: uzunluk → gürültü → problem odağı.
 * Yeni filtre = Filter uygulayan sınıf + aşağıdaki listeye ekle.
 */
class FilterPipeline
{
    /** @var Filter[] */
    private array $filters;

    public function __construct(?array $filters = null)
    {
        $this->filters = $filters ?? [
            new LengthFilter(),
            new NoiseFilter(),
            new ProblemFocusFilter(),
        ];
    }

    /** Tüm filtrelerden geçerse true; ilk red → false. */
    public function passes(array $item, array $feed): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->passes($item, $feed)) {
                return false;
            }
        }
        return true;
    }
}
