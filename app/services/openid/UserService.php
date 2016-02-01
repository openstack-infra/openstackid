<?php
namespace services\openid;

use auth\IUserRepository;
use auth\User;
use Exception;
use openid\model\IOpenIdUser;
use openid\services\IUserService;
use utils\db\ITransactionService;
use utils\services\ILogService;

/**
 * Class UserService
 * @package services\openid
 */
final class UserService implements IUserService
{

    const USER_NAME_INVALID_CHAR_REPLACEMENT = '.';

    const USER_NAME_CHAR_CONNECTOR           = '.';

    private static  $convert_table = array(
        '&amp;' => 'and',   '@' => 'at',    '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae','Ç' => 'c',
        'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß' => 'ss','à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'ae','ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
        'ā' => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
        'ć' => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
        'č' => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
        'ē' => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
        'ę' => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
        'ğ' => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
        'ĥ' => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
        'ī' => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
        'ı' => 'i', 'Ĳ' => 'ij','ĳ' => 'ij','Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
        'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
        'Ľ' => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
        'Ń' => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
        'ŉ' => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
        'ŏ' => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe','œ' => 'oe','Ŕ' => 'r',
        'ŕ' => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
        'ś' => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
        'š' => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
        'ŧ' => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
        'ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
        'ų' => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
        'Ź' => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
        'ſ' => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
        'ư' => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
        'ǒ' => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
        'ǘ' => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
        'ǻ' => 'a', 'Ǽ' => 'ae','ǽ' => 'ae','Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
        'Ё' => 'jo','Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
        'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh','З' => 'z',
        'И' => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
        'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch','Ш' => 'sh','Щ' => 'sch',
        'Ъ' => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je','Ю' => 'ju','Я' => 'ja',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ж' => 'zh','з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh','щ' => 'sch','ъ' => '-','ы' => 'y', 'ь' => '-', 'э' => 'je',
        'ю' => 'ju','я' => 'ja','ё' => 'jo','є' => 'e', 'і' => 'i', 'ї' => 'i',
        'Ґ' => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
        'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
        'ך' => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
        'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
        'צ' => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
    );

    public static function normalizeChars($input) {
        return strtr($input, self::$convert_table);
    }

    /**
     * @var IUserRepository
     */
    private $repository;
    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @param IUserRepository $repository
     * @param ITransactionService $tx_service
     * @param ILogService $log_service
     */
    public function __construct(IUserRepository $repository, ITransactionService $tx_service, ILogService $log_service)
    {
        $this->repository = $repository;
        $this->log_service = $log_service;
        $this->tx_service = $tx_service;
    }


    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function updateLastLoginDate($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->last_login_date = gmdate("Y-m-d H:i:s", time());
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function updateFailedLoginAttempts($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->login_failed_attempt += 1;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function lockUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {

                $user->lock = true;
                $this->repository->update($user);

                Log::warning(sprintf("User %d locked ", $identifier));
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function unlockUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {

                $user->lock = false;
                $this->repository->update($user);

                Log::warning(sprintf("User %d unlocked ", $identifier));
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function activateUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->active = true;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function deActivateUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->active = false;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @param $show_pic
     * @param $show_full_name
     * @param $show_email
     * @return bool
     * @throws \Exception
     */
    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->public_profile_show_photo = $show_pic;
                $user->public_profile_show_fullname = $show_full_name;
                $user->public_profile_show_email = $show_email;

                return $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }

        return false;
    }

    public function get($id)
    {
        return $this->repository->get($id);
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        return $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
    }

    /**
     * @param \Member $member
     * @return IOpenIdUser
     */
    public function buildUser(\Member $member)
    {
        $repository = $this->repository;
        return $this->tx_service->transaction(function () use($member, $repository){
            //create user
            $user                       = new User();
            $user->external_identifier  = $member->ID;
            $user->identifier           = $member->ID;
            $user->last_login_date      = gmdate("Y-m-d H:i:s", time());
            $user->active               = true;
            $user->lock                 = false;
            $user->login_failed_attempt = 0;

            $repository->add($user);

            $fname = UserService::normalizeChars($member->FirstName);
            $lname = UserService::normalizeChars($member->Surname);
            $proposed_username     = strtolower
            (
                preg_replace('/[^\d\w]+/i', UserService::USER_NAME_INVALID_CHAR_REPLACEMENT, $fname)
                . UserService::USER_NAME_CHAR_CONNECTOR .
                preg_replace('/[^\d\w]+/i', UserService::USER_NAME_INVALID_CHAR_REPLACEMENT, $lname)
            );

            $done                  = false;
            $fragment_nbr          = 1;
            $aux_proposed_username = $proposed_username;
            do
            {

                $old_user = $repository->getOneByCriteria
                (
                    array
                    (
                        array('name' => 'identifier', 'op' => '=', 'value' => $aux_proposed_username),
                        array('name' => 'id', 'op' => '<>', 'value' => $user->id)
                    )
                );

                if (is_null($old_user))
                {

                    $user->identifier = $aux_proposed_username;
                    $done = $repository->update($user);
                }
                else
                {
                    $aux_proposed_username = $proposed_username . UserService::USER_NAME_CHAR_CONNECTOR . $fragment_nbr;
                    $fragment_nbr++;
                }

            } while (!$done);

            return $user;
        });
    }

}