<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Leaves</title>
    <style>
        .staff-leaves {
            width: 100%;
        }

        .staff-leaves table {
            background: transparent;
            backdrop-filter: blur(10px);
            border-collapse: collapse;
            max-height: 100%;
        }

        .staff-leaves table th {
            padding: 8px;
            font-size: 18px;
            font-weight: 700;
            max-width: 150px;
            color: var(--white-color--);
            background: #da2442;
            text-align: center;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .staff-leaves table td{
            color: black;
            font-weight: 400;
            font-size: 16px;
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid gray;
        }
        .staff-leaves table tbody tr:nth-last-of-type(even){
            background-color:rgba(218, 36, 66, 0.22);
        }
        .staff-leaves table tbody tr:last-of-type{
            border-bottom: 2px solid var(--brand-color--);
        }
        </style>
    </head>

<body>
    <?= view('navbar/sidebar') ?>
    <section class="container staff-leaves">
    <h2>Staff Leaves | Compensations</h2>
    <table>
            <thead>
                <tr>

                    <th>ID</th>
                    <th>Name</th>
                    <?php
                    foreach (array_reverse($oe) as $period => $status):
                    ?>
                        <th><?= $period ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data as $empId => $details): ?>
                        <tr>
                            <td><?= htmlspecialchars($empId) ?></td>
                            <td><?= htmlspecialchars($details['name']) ?></td>
                            <?php foreach (array_reverse($oe) as $period => $status): ?>
                                <td>
                                    <?php
                                    if (isset($details['records'][$period])):
                                        echo $details['records'][$period]['leaves'] . " | " . $details['records'][$period]['compensation'];
                                    else:
                                        echo "-";
                                    endif;
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= count($oe) + 1 ?>">No data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>

</html>