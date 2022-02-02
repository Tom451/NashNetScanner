<?php
 <div class="col-md-12">
                <div>
                    <div class="collapse hide" id="collapse-1" style="padding-top: 30px">

                        <h2>Vulnerbilities</h2>

                        <table id="Vulnerbilities" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                <th>Vulnerbility Name</th>
                                <th>Complexity</th>
                                <th>Port Name</th>
                                <th>Serverty</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            foreach ($vulns as $item) {

                                if ($item['VulnCPE'] == "NO CPE"){

                                }
                                else{


                                    $UPLOADString = "https://services.nvd.nist.gov/rest/json/cves/1.0/?cpeMatchString=".$item['VulnCPE'];
                                    $JSON = file_get_contents($UPLOADString);
                                    $JSONObject = json_decode($JSON);

                                    if ($JSONObject -> totalResults != 0){

                                        $CVEItems = $JSONObject -> result ->CVE_Items;



                                        foreach ($CVEItems as $CVE) {
                                            echo '<tr>';
                                            echo '<td>' . $CVE -> cve -> CVE_data_meta -> ID . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->attackComplexity . '</td>';
                                            echo '<td>' . $item['VulnName'] . '</td>';
                                            echo '<td>' . $CVE ->impact->baseMetricV3->cvssV3->baseSeverity . '</td>';
                                            echo '<tr>';
                                        }
                                    }
                                    else{

                                    }







                                }



                            }

                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <p>&nbsp;</p>