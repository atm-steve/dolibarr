#!/bin/bash


cd ..
CURRENTDIR=($(pwd))
#On supprime server_installer
#sudo rm -R server_installer
# et on le reclone
#git clone git@gogs.atm-consulting.fr:ATM-Consulting/server_installer.git

#On prepare le répertoire pour les cloner
sudo rm -R modules_git
mkdir modules_git

# On récupére la liste des module depuis server installer
TAB=($(cat server_installer/dolibarr/modules/list.ini | grep '^\[' | tr -d '[]' | grep -v global))
declare -A TModule
TAG=""
# on construit un tableau avec les depots, modules et branche
while read p; do
    if [[ ${p} =~ ^\[ ]]; then
        I=0
        TAG=${p#"["}
        TAG=${TAG%"]"}
    elif [[ ${p} =~ ^\; ]]; then
        :
    elif [[ -n ${TAG} ]] && [[ -n ${p} ]] && [[ ${p} =~ "github" ]]; then
        TModule[${TAG}, ${I}]=${p}
        ((I++))
    fi
done < server_installer/dolibarr/modules/list.ini
# on va cloner les modules global de list.ini dans modules_git
cd modules_git
for TAG in "global"; do
    J=0
    while :; do
        if [[ ${TModule[${TAG}, ${J}]+ok} ]]; then
            IFS=' '; arrIN=(${TModule[${TAG}, ${J}]}); unset IFS;

            URL=${arrIN[0]}

            if [[ ${arrIN[1]+ok} ]]; then NAME=${arrIN[1]}
            else NAME=${arrIN[0]##*/}; NAME=${NAME%".git"}
            fi

            if [[ ${arrIN[2]+ok} ]]; then BRANCH=${arrIN[2]}
            else BRANCH="master"
            fi

            printf "${CINFO}git clone ${URL} ${NAME} ${NC}\n"
            git clone ${URL} ${NAME}

            # check if clone success
            if [ $? -eq 0 ]; then
                cd ${NAME}
                if [[ ${BRANCH} != $(git name-rev --name-only HEAD) ]]; then
                    printf "${CINFO}git checkout ${BRANCH} ${NC}\n"
                    git checkout ${BRANCH}
                fi
                cd ..
            fi

            echo ""
        else
            break
        fi
        ((J++))
    done
done

cd "${CURRENTDIR}"
echo "${CURRENTDIR}"
rm -Rf dolibarr/htdocs/custom/*
mv modules_git/* dolibarr/htdocs/custom/

cd dolibarr/htdocs/custom/
sudo find . -name ".git" -type d -exec rm -rf "{}" \;
sudo find . -name ".gitignore" -type f -exec rm -f "{}" \;
cd "${CURRENTDIR}"/dolibarr
git add *
git commit -am"newversion"
#git push atm 11.0_scrutinizer



