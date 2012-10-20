<?php 

/**
 * ページネイション用のクラスです。
 * @author miztaka
 *
 */
class Teeple_Pager {
    
    /** ページ番号です。 1から始まります。 */
    public $page = 1;
    
    /** 1ページの表示件数です。 */ 
    public $limit = 0;
    
    /** 全件数です。 */
    public $total = 0;
    
    /** 今回取得した件数です。 */
    public $hit = 0;

    /** 現在のページの最初のレコード番号です。 */
    public function first() {
        return ($this->page - 1) * $this->limit + 1;
    }
    
    /** 現在のページの最後のレコード番号です。 */
    public function last() {
        $max = $this->first() + $this->limit - 1;
        return $max > $this->total ? $this->total : $max;
    }
    
    /** 次のページがあるか？ */
    public function hasNext() {
        return $this->last() < $this->total;
    }
    
    /** 前のページがあるか？ */
    public function hasPrev() {
        return $this->first() > 1;
    }
    
    /** 次のページ番号を返します。 */
    public function nextPage() {
        return $this->page + 1;
    }
    
    /** 前のページ番号を返します。 */
    public function prevPage() {
        return $this->page - 1;
    }
        
    /** 全ページ数 */
    public function numOfPages() {
        $div = floor($this->total / $this->limit);
        $amari = $this->total % $this->limit;
        if ($amari > 0) {
            $div += 1;
        }
        return $div;
    }
    
    /** レコードのオフセットを取得します。 (0からはじまる) */
    public function offset() {
        return ($this->page - 1) * $this->limit;
    }
}
