<?php

namespace App\Controller;

use App\Entity\Dictionary;
use App\Entity\History;
use App\Repository\DictionaryRepository;
use App\Repository\HistoryRepository;
use App\Service\SearchImage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(ManagerRegistry $doctrine, Request $request, HistoryRepository $searchHistoryRepository, DictionaryRepository $dictionaryRepository, SearchImage $searchImage): Response
    {
        $loginUser = $this->getUser()->getUserIdentifier();  //loginUser情報の取得
        if ($loginUser == NULL) {
            return $this->renderForm('login/index.html.twig');  //nullだったらloginに戻る
        }

        $histories = $searchHistoryRepository->findBy(['username' => $loginUser]);
        $dictionary = new Dictionary();
        $form = $this->createFormBuilder($dictionary)
            ->add('word', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Search'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search_word = $form->get('word')->getData();
            $item = $dictionaryRepository->findOneBy(['word' => $search_word]);  //キーワードで検索しその情報を取得
            $loginUser = $this->getUser()->getUserIdentifier();

            if ($item) {
                $loginUser = $this->getUser()->getUserIdentifier();
                $mean = $item->getMean();   //取得した情報の中からmeanを取得

                $html = $searchImage->getSearchImage($search_word);  //Serviceから読み込んだgetSearchImageを使って検索先のhtml情報を取得
                $html = explode(",", $html);  //取得した情報を","で区切って配列に入れる
                $cnt = count($html);          //配列の要素数をカウント
                $needle = "1400w";            //取得したい画像を取得するためのkeyword
                $s = 0;                       //for文を回すための変数
                $searched_image = array();   //配列の定義
                for ($i = 0; $i < $cnt; $i++) {                   //for文で各要素をチェック
                    if (strpos($html[$i], $needle) === false) {  //keywordを含んだ配列があるかチェック
                        continue;       //なければコンティニュー
                    } else {
                        $searched_image[$s] = $html[$i];  //あれば配列$searched_imageに入れる
                        if ($s < 3) {                     //3枚分を取得
                            $s++;                         //取得したら１つ増やして次に。
                        } else {
                            continue;        //無ければコンティニュー
                        }
                    }
                }

                //検索履歴をHistoryに追加する
                $entityManager = $doctrine->getManager();   //entityManagerを準備する
                $history = new History();                   //追加するためのデータ枠を作成
                $history->setUsername($loginUser);       //ユーザーネームをセット
                $history->setWord($search_word);                   //英単語をセット
                $history->setMean($mean);                   //意味をセット

                $entityManager->persist($history);  //指示をセット
                $entityManager->flush();            //指示を完了

                //検索履歴を取得する
                $histories = $searchHistoryRepository->findBy(['username' => $loginUser]);

                return $this->renderForm('search/index.html.twig', ['histories' => $histories, 'searched_image' => $searched_image,
                    'form' => $form, 'word' => $search_word, 'mean' => $mean, 'loginUser' => $loginUser,]);

                //検索した単語がなかった場合のReturn
            } else {
                $mean = " ";
                $word = " ";
                $search_word = "";
                $searched_image = "";
                return $this->renderForm('search/index.html.twig', [
                    'loginUser' => $loginUser,
                    'dictionary' => $dictionary,
                    'form' => $form,
                    'mean' => $mean,
                    'word' => $word,
                    'searched_image' => $searched_image,
                    'search_word' => $search_word,
                    'histories' => $histories,]);
            }
            //入力した文字が有効出なかった場合のReturn
        }else {
            $mean = " ";
            $word = " ";
            $search_word = "";
            $searched_image = "";
            return $this->renderForm('search/index.html.twig', [
                'loginUser' => $loginUser,
                'dictionary' => $dictionary,
                'form' => $form,
                'mean' => $mean,
                'word' => $word,
                'searched_image' => $searched_image,
                'search_word' => $search_word,
                'histories' => $histories,]);


        }
        //ログイン時のReturn
        $mean = " ";
        $word = " ";
        $search_word = "";
        $searched_image = "";
        return $this->renderForm('search/index.html.twig', [
            'loginUser' => $loginUser,
            'dictionary' => $dictionary,
            'form' => $form,
            'mean' => $mean,
            'word' => $word,
            'searched_image' => $searched_image,
            'search_word' => $search_word,
            'histories' => $histories,
        ]);
    }

    #[Route('/deleteHistory', name: 'app_deleteHistory', methods: ['POST'])]
    public function deleteHistory(Request $request, ManagerRegistry $doctrine): Response
    {
        $id = $request->get('history_id');

        $entityManager = $doctrine->getManager();
        $history = $entityManager->getRepository(History::class)->find($id);
        if (!$history) {
            throw $this->createNotFoundException(
                'No History hare');
        }
        $entityManager->remove($history);
        $entityManager->flush();
        return $this->redirectToRoute('app_search');
    }
}