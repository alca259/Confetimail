<?php

class HomeController extends BaseController
{
    #region Variables privadas
    private $controllerName;
    #endregion

    #region Constructors
    public function __construct()
    {
        // Llamamos al constructor padre
        parent::__construct();
        $this->controllerName = str_replace("Controller", "", __CLASS__);
    }
    #endregion

    #region Controller actions
    /**
     * Carga la pantalla principal
     * @author alca259
     * @version OK
     */
    public function Index()
    {
        // Cargamos la temática actual
        $currentMailIds = $this->mailModel->Search(ROOT_USER, array(
            array("is_confeti", "=", 1),
            array("active", "=", 1)
        ), "date_send DESC");
        
        if (!empty($currentMailIds))
        {
            $currentMail = $this->mailModel->Browse(ROOT_USER, array($currentMailIds[0]));
            $this->ViewBag->Tematica = $currentMail[0]["tematica"];
            $this->ViewBag->TematicaDesc = $currentMail[0]["tematica_desc"];
            $this->ViewBag->TematicaImageUrl = $currentMail[0]["image_carousel"];
            
            // Cargamos los confetis anteriores
            $mailIds = $this->mailModel->Search(ROOT_USER, array(
                array("is_confeti", "=", 1),
                array("active", "=", 1),
                array("id", "not in", array($currentMailIds[0]))
            ));
        }
        else
        {
            // Cargamos los confetis anteriores
            $mailIds = $this->mailModel->Search(ROOT_USER, array(
                array("is_confeti", "=", 1),
                array("active", "=", 1)
            ));
        }

        if (!empty($mailIds))
        {
            $this->ViewBag->Mails = $this->mailModel->Browse(ROOT_USER, $mailIds, "date_send DESC");
        }
        
        // Establecemos una publicación de blog por defecto
        $this->ViewBag->CarouselBlogText = T_("Carousel.Blog.Text");
        $this->ViewBag->CarouselBlogUrl = StringUtil::UrlAction("", "Blog");
        $this->ViewBag->CarouselBlogImageUrl = "/Public/img/content/carousel_blog.jpg";
        
        // Recuperamos la ultima publicacion del blog
        $lastBlogPostSearch = $this->blogPostModel->Search(ROOT_USER, array(
            array("active", "=", 1),
        ), "date_published DESC", "1");
        
        if (!empty($lastBlogPostSearch))
        {
            $lastBlogPost = $this->blogPostModel->Browse(ROOT_USER, $lastBlogPostSearch);
            
            $this->ViewBag->CarouselBlogText = utf8_encode($lastBlogPost[0]["subject"]);
            $this->ViewBag->CarouselBlogUrl = sprintf("%s/%s", StringUtil::UrlAction("Reading", "Blog"), $lastBlogPost[0]["id"]);
            $this->ViewBag->CarouselBlogImageUrl = $lastBlogPost[0]["image_frontend"];
        }
        
        $this->ViewBag->Title = T_("Home");
        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag);
    }

    /**
     * Instala las tablas básicas en el entorno mysql
     * @author alca259
     * @version OK
     */
    public function Install()
    {
        $this->ViewBag->Error = "";
        $this->ViewBag->Done = false;
        $this->ViewBag->Title = T_("New install");

        if (empty($_POST))
        {
            return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, "", false, true);
        }

        // Administration password
        if (isset($_POST['confirm']) && $_POST['confirm'] == ADMIN_PASS)
        {
            // Inicializamos nuestras tablas básicas en base de datos
            // Cuentas
            $this->LoadModel('Account', "", true);
            // Modelos
            $mModel = $this->LoadModel('Ir_Model', Constants::$PanelAreaName, true);
            // Accesos
            $this->LoadModel('Ir_Model_Access', Constants::$PanelAreaName, true);
            // Ficheros
            $this->LoadModel('File', Constants::$PanelAreaName, true);
            // Mail
            $this->LoadModel('Mail', Constants::$PanelAreaName, true);
            // Cuentas de Mail
            $this->LoadModel('Mail_Account', Constants::$PanelAreaName, true);
            // Ficheros de Mail
            $this->LoadModel('Mail_File', Constants::$PanelAreaName, true);
            // Entradas de blog
            $this->LoadModel('Blog_Post', Constants::$PanelAreaName, true);
            // Comentarios de blog
            $this->LoadModel('Blog_Post_Comment', Constants::$PanelAreaName, true);
            // Entradas de comentarios
            $this->LoadModel('Review_Post', Constants::$PanelAreaName, true);
            // Encuestas de usuario
            $this->LoadModel('Survey_Account', Constants::$PanelAreaName, true);
            
            // Creamos la busqueda de validacion
            $domain_account = array(array("name", "=", "Account"));
            $domain_model = array(array("name", "=", "Ir_Model"));
            $domain_access = array(array("name", "=", "Ir_Model_Access"));
            $domain_file = array(array("name", "=", "File"));
            $domain_mail = array(array("name", "=", "Mail"));
            $domain_mail_account = array(array("name", "=", "Mail_Account"));
            $domain_mail_file = array(array("name", "=", "Mail_File"));
            $domain_blog_post = array(array("name", "=", "Blog_Post"));
            $domain_review_post = array(array("name", "=", "Review_Post"));

            // Buscamos aquellos modelos que coincidan
            $sModelAccount = $mModel->Search(ROOT_USER, $domain_account);
            $sModelModel = $mModel->Search(ROOT_USER, $domain_model);
            $sModelAccess = $mModel->Search(ROOT_USER, $domain_access);
            $sModelFile = $mModel->Search(ROOT_USER, $domain_file);
            $sModelMail = $mModel->Search(ROOT_USER, $domain_mail);
            $sModelMailAccount = $mModel->Search(ROOT_USER, $domain_mail_account);
            $sModelMailFile = $mModel->Search(ROOT_USER, $domain_mail_file);
            $sModelBlogPost = $mModel->Search(ROOT_USER, $domain_blog_post);
            $sModelReviewPost = $mModel->Search(ROOT_USER, $domain_review_post);

            // Si está vacio, insertamos
            if (empty($sModelAccount)) { $mModel->Create(ROOT_USER, array('name' => "Account", 'active' => true)); }
            if (empty($sModelModel)) { $mModel->Create(ROOT_USER, array('name' => "Ir_Model", 'active' => true)); }
            if (empty($sModelAccess)) { $mModel->Create(ROOT_USER, array('name' => "Ir_Model_Access", 'active' => true)); }
            if (empty($sModelFile)) { $mModel->Create(ROOT_USER, array('name' => "File", 'active' => true)); }
            if (empty($sModelMail)) { $mModel->Create(ROOT_USER, array('name' => "Mail", 'active' => true)); }
            if (empty($sModelMailAccount)) { $mModel->Create(ROOT_USER, array('name' => "Mail_Account", 'active' => true)); }
            if (empty($sModelMailFile)) { $mModel->Create(ROOT_USER, array('name' => "Mail_File", 'active' => true)); }
            if (empty($sModelBlogPost)) { $mModel->Create(ROOT_USER, array('name' => "Blog_Post", 'active' => true)); }
            if (empty($sModelReviewPost)) { $mModel->Create(ROOT_USER, array('name' => "Review_Post", 'active' => true)); }

            // marcamos OK
            $this->ViewBag->Done = true;
        }
        else
        {
            $this->ViewBag->Error = T_("Invalid password");
        }

        return new View(__FUNCTION__, $this->controllerName, $this->ViewBag, "", false, true);
    }
    #endregion
}