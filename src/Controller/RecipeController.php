<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Picture;
use App\Form\RecipeType;
use App\Entity\Ingredient;
use App\Service\PictureService;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/recipe')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'app_recipe_index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipeRepository->findAll(),
        ]);
    }

    #[Route('/myRecipes', name: 'app_recipe_myrecipes', methods: ['GET'])]
    public function getMyRecipe(RecipeRepository $recipeRepository): Response
    {
        $user = $this->getUser();
        $recipes = $recipeRepository->findByUserId($user->getId());
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/new', name: 'app_recipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PictureService $pictureService): Response
    {
        $user = $this->getUser();
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $imgs = $form->get('pictures')->getData();
            $ingredients = $form->get('ingredients')->getData();
            $recipe->setAuthor($user);
            $recipe->setTimestamps();
            if ($ingredients) {
                $listIngredients = explode(';', $ingredients);

                foreach ($listIngredients as $ingredient) {
                    $ingredientWithQuantity = explode(':', $ingredient);
                    if (isset($ingredientWithQuantity[0]) && isset($ingredientWithQuantity[1])) {
                        $quantity = preg_replace('/\s+/', '', $ingredientWithQuantity[1]);
                        $ingredient = new Ingredient();
                        $ingredient->setName($ingredientWithQuantity[0]);
                        $ingredient->setQuantity($quantity);
                        $recipe->addIngredient($ingredient);
                    }
                }
            }

            if ($imgs) {
                $folder = "recipes";
                $index = 0;
                foreach ($imgs as $img) {
                    $fichier = $pictureService->add($img, $folder, 300, 300);
                    $picture = new Picture();
                    $picture->setName("image");
                    $picture->setPath($fichier);
                    if ($index == 0) {
                        $picture->setPrincipalPicture(true);
                    } else {
                        $picture->setPrincipalPicture(false);
                    }
                    $recipe->addPicture($picture);
                    $index++;
                }
            }

            $entityManager->persist($recipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $recipe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
