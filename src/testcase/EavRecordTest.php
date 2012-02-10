<?php

class EavRecordTest extends UnitTestCase
{
    
    /*
    public function testFind() {
        
        $record = Teeple_EavRecord::find(5);
        print $record->title;
    }
    */
    
    public function testSelect() {
        
        // まったく条件を指定しないSELECT
        $result = Teeple_EavRecord::neu("test")->select();
        $this->assertEqual(6, count($result));
        $this->assertEqual(12, $result[2]->id);
        $this->assertEqual("公開日が空のデータ", $result[0]->field1);
        
        // 公開中のみ
        $result = Teeple_EavRecord::neu("test")->select(true);
        $this->assertEqual(4, count($result));
        
        // カラム１、カラム２で検索
        $result = Teeple_EavRecord::neu("test")
            ->contains("field1", "公開")
            ->eq("field2", "ほげほげ")
            ->select(true);
        foreach ($result as $one) {
            print("{$one->id} {$one->field1} {$one->field2}\n");
        }
        
        // ページネーション
        $result = Teeple_EavRecord::neu("test")
            ->limit(2)
            ->offset(2)
            ->select();
        $this->assertEqual(2, count($result));
        $this->assertEqual(11, $result[1]->id);
        
    }
    
    public function testSelect2() {
        
        $result = Teeple_EavRecord::neu("test")
            ->ge('publish_start_dt', '2011-01-01 00:00:00')
            ->lt('publish_start_dt', '2012-01-01 00:00:00')
            ->contains("field1", "公開")
            ->select();
        $this->assertEqual(1, count($result));
        
    }
    
    public function testSelect3() {
        
        $result = Teeple_EavRecord::neu("test")
            ->ge('publish_start_dt', '2011-01-01 00:00:00')
            ->lt('publish_start_dt', '2012-01-01 00:00:00')
            ->contains("field1", "非公開")
            ->select();
        $this->assertEqual(0, count($result));
    }
    
    public function testSelect4() {
        
        $result = Teeple_EavRecord::neu("test")
            ->ge('publish_start_dt', '2010-01-01 00:00:00')
            ->lt('publish_start_dt', '2011-01-01 00:00:00')
            ->contains("field1", "公開")
            ->select();
        $this->assertEqual(0, count($result));
    }
    
}