<?php

namespace App\Controller;

use App\Entity\Dictionary;
use App\Form\DictionaryType;
use App\Repository\DictionaryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//Dictionaryへの登録、変更、削除をを行うコントローラー(crud)で作成
#[Route('/edit/dictionary')]
class EditDictionaryController extends AbstractController
{
    #[Route('/', name: 'app_edit_dictionary_index', methods: ['GET'])]
    public function index(DictionaryRepository $dictionaryRepository): Response
    {
        return $this->render('edit_dictionary/index.html.twig', [
            'dictionaries' => $dictionaryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_edit_dictionary_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DictionaryRepository $dictionaryRepository): Response
    {
        $dictionary = new Dictionary();
        $form = $this->createForm(DictionaryType::class, $dictionary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dictionaryRepository->add($dictionary, true);

            return $this->redirectToRoute('app_edit_dictionary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('edit_dictionary/new.html.twig', [
            'dictionary' => $dictionary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_edit_dictionary_show', methods: ['GET'])]
    public function show(Dictionary $dictionary): Response
    {
        return $this->render('edit_dictionary/show.html.twig', [
            'dictionary' => $dictionary,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_edit_dictionary_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dictionary $dictionary, DictionaryRepository $dictionaryRepository): Response
    {
        $form = $this->createForm(DictionaryType::class, $dictionary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dictionaryRepository->add($dictionary, true);

            return $this->redirectToRoute('app_edit_dictionary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('edit_dictionary/edit.html.twig', [
            'dictionary' => $dictionary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_edit_dictionary_delete', methods: ['POST'])]
    public function delete(Request $request, Dictionary $dictionary, DictionaryRepository $dictionaryRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $dictionary->getId(), $request->request->get('_token'))) {
            $dictionaryRepository->remove($dictionary, true);
        }

        return $this->redirectToRoute('app_edit_dictionary_index', [], Response::HTTP_SEE_OTHER);
    }
}
