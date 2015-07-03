<?php

namespace services\oauth2;

use Client;
use oauth2\exceptions\AbsentClientException;
use oauth2\models\IUserConsent;
use oauth2\services\IUserConsentService;
use UserConsent;

class UserConsentService implements IUserConsentService
{

    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent
     */
    public function get($user_id, $client_id, $scopes)
    {

        $set = explode(' ', $scopes);
        $size = count($set) - 1;
        $perm = range(0, $size);
        $j = 0;
        $perms = array();

        do
        {
            foreach ($perm as $i)
            {
                $perms[$j][] = $set[$i];
            }
        } while ($perm = $this->nextPermutation($perm, $size) and ++$j);

        $scope_conditions = array();

        $query1 = UserConsent::where('user_id', '=', $user_id)->where('client_id', '=', $client_id);

        $query2 = UserConsent::where('user_id', '=', $user_id)->where('client_id', '=', $client_id);


        $query1 = $query1->where(function ($query) use($perms)
        {
            foreach ($perms as $p)
            {
                $str = join(' ', $p);
                $query = $query->orWhere('scopes', '=', $str);
            }

            return $query;
        });


        $query2 = $query2->where(function ($query) use($perms)
        {
            foreach ($perms as $p)
            {
                $str = join(' ', $p);
                $query = $query->orWhere('scopes', 'like', '%'.$str.'%');
            }

            return $query;
        });


        $consent = $query1->first();

        if (is_null($consent)) {
            $consent = $query2->first();
        }

        return $consent;
    }


    /**
     * @param $p
     * @param $size
     * @return bool
     *
     * http://docstore.mik.ua/orelly/webprog/pcook/ch04_26.htm
     *
     */
    private function nextPermutation($p, $size)
    {
        // slide down the array looking for where we're smaller than the next guy
        for ($i = $size - 1; $i >= 0 && $p[$i] >= $p[$i + 1]; --$i) {}

        // if this doesn't occur, we've finished our permutations
        // the array is reversed: (1, 2, 3, 4) => (4, 3, 2, 1)
        if ($i == -1)
        {
            return false;
        }

        // slide down the array looking for a bigger number than what we found before
        for ($j = $size; $p[$j] <= $p[$i]; --$j) {}

        // swap them
        $tmp   = $p[$i];
        $p[$i] = $p[$j];
        $p[$j] = $tmp;

        // now reverse the elements in between by swapping the ends
        for (++$i, $j = $size; $i < $j; ++$i, --$j)
        {
            $tmp = $p[$i];
            $p[$i] = $p[$j];
            $p[$j] = $tmp;
        }

        return $p;
    }


    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent|void
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function add($user_id, $client_id, $scopes)
    {
        $consent = new UserConsent();

        if (is_null(Client::find($client_id))) {
            throw new AbsentClientException;
        }

        $consent->client_id = $client_id;
        $consent->user_id = $user_id;
        $consent->scopes = $scopes;
        $consent->Save();
    }
}